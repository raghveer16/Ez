<?php

namespace EzAd\Studio;

use EzAd\Util\Geometry\Size;

class Canvas
{
    /** @var int */
    private $width;

    /** @var int */
    private $height;

    private $bgColor = 0x000000;

    private $bgImage;

    private $bgType = 'color';

    /** @var AbstractItem[] */
    private $items = [];

    private $duration = -1;

    public function __construct($width, $height, $duration = -1)
    {
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
    }

    public static function fromArraySpec(array $spec)
    {
        // for now, only support 720p, 1080 takes 2x longer to encode, files are 2x bigger
        $width = 1280;//$spec['width'];
        $height = 720;//$spec['height'];
        $self = new static($width, $height);

        $self->duration = $spec['duration'];

        if ( $spec['backgroundImage'] ) {
            $self->setBackgroundImage($spec['backgroundImage']);
        } else if ( $spec['backgroundColor'] ) {
            $self->setBackgroundColor(hexdec(ltrim($spec['backgroundColor'], '#')));
        } else {
            $self->setBackgroundColor(0x000000);
        }

        foreach ( $spec['items'] as $item ) {
            if ( $item['type'] == 'video' ) {
                $it = new VideoItem();
                $it->setSource($item['source'])
                    ->setSeekTo($item['startPos'])
                    ->setTimeRange($item['timeRange'][0], $item['timeRange'][1])
                    ->setRect($item['rect'])
                    ->setLayerIndex($item['layerIndex'])
                    ->setEndBehavior('repeat');
            } else if ( $item['type'] == 'image' ) {
                $it = new ImageItem();
                $it->setSource($item['source'])
                    ->setTimeRange($item['timeRange'][0], $item['timeRange'][1])
                    ->setRect($item['rect'])
                    ->setLayerIndex($item['layerIndex']);
            } else {
                continue;
            }

            $self->addItem($it);
        }

        return $self;
    }

    public function setBackgroundColor($color)
    {
        $this->bgType = 'color';
        $this->bgColor = $color;
    }

    public function setBackgroundImage($path)
    {
        $this->bgType = 'image';
        $this->bgImage = $path;
    }

    public function addItem(AbstractItem $item)
    {
        if ( $item->getTimeEnd() > 900 ) {
            $item->setTimeEnd(900);
        }
        if ( $item->getTimeStart() >= $item->getTimeEnd() ) {
            return;
        }

        $this->items[] = $item;
    }

    private function sortItems()
    {
        usort($this->items, function($a, $b) {
            return $a->getLayerIndex() - $b->getLayerIndex();
        });
    }

    public function getDuration()
    {
        if ( $this->duration > 0 && $this->duration < 900 ) {
            return $this->duration;
        }

        // highest timeEnd of all items?
        return max(array_map(function($item) {
            return $item->getTimeEnd();
        }, $this->items));
    }

    public function generateAudioFilterGraph()
    {
        $graph = new Filter\FilterGraph();

        $silence = FilterHelper::silence($this->getDuration(), 'silence');
        $graph->add($silence);

        // thinking on it, might want to come up with a different way since amix allows as many
        // inputs as we want.
        // basically, create an audio stream for each video, pad it at start/end, then amix together.
        // or just pad at the start via aevalsrc=0, add an apad to the end of the audio graph, and
        // use -shortest in the command?
        // or maybe the easiest method, use $silence as the first in amix, and set duration=first.

        /*
        Concatenate an opening, an episode and an ending, all in bilingual version (video in stream 0, audio in streams 1 and 2):
        ffmpeg -i opening.mkv -i episode.mkv -i ending.mkv -filter_complex \
          '[0:0] [0:1] [0:2] [1:0] [1:1] [1:2] [2:0] [2:1] [2:2]
           concat=n=3:v=1:a=2 [v] [a1] [a2]' \
          -map '[v]' -map '[a1]' -map '[a2]' output.mkv
        Concatenate two parts, handling audio and video separately, using the (a)movie sources, and adjusting the resolution:
        movie=part1.mp4, scale=512:288 [v1] ; amovie=part1.mp4 [a1] ;
        movie=part2.mp4, scale=512:288 [v2] ; amovie=part2.mp4 [a2] ;
        [v1] [v2] concat [outv] ; [a1] [a2] concat=v=0:a=1 [outa]
        Note that a desync will happen at the stitch if the audio and video streams do not have exactly the same duration in the first file.
        */

        $inputs = 1;
        $inLabels = ['silence'];
        foreach ( $this->items as $index => $item ) {
            // video is the only one we care about, extract the audio from it, process it, etc.
            if ( $item instanceof VideoItem ) {
                if ( !$item->getSource() ) {
                    continue;
                }

                $seekTo = $item->getSeekTo();
                $timeStart = $item->getTimeStart();
                $timeEnd = $item->getTimeEnd();
                $vduration = $timeEnd - $timeStart;
                $volume = $item->getVolume();

                if ( $volume <= 0 ) {
                    continue;
                }

                /*if ( $seekTo == 0 && $timeStart == 0 ) {
                    $chain = FilterHelper::audioZero($item->getSource(), "aout$inputs");
                } else {
                    $chain = FilterHelper::audioOffset($item->getSource(), $timeStart, $seekTo, "aout$inputs");
                }*/

                $chain = new Filter\FilterChain('', "aout$inputs");
                $chain->add(Filter\AudioSrc::create($item->getSource()), 'audio');

                $chain->add(Filter\Setpts::createZero()->setIsAudio(true));
                // only add an output label if we're going to use it.
                $chain->add(Filter\Freeform::create('atrim', ['start' => $seekTo, 'end' => $seekTo + $vduration],
                    '', ($timeStart > 0 || $volume < 1.0) ? "asrc$inputs" : ''));

                // add silence to the start of the audio stream
                if ( $timeStart > 0 ) {
                    //$chain->add(Filter\Setpts::createOffset($timeStart - $seekTo, '', "asrc$inputs")->setIsAudio(true));

                    $chain->add(FilterHelper::silence($timeStart, "sil$inputs"));
                    $chain->add(Filter\Freeform::create('concat', ['v' => 0, 'a' => 1], "sil$inputs,asrc$inputs"));
                }

                if ( $volume < 1.0 ) {
                    $volumeFilter = new Filter\Volume($timeStart > 0 ? '' : "asrc$inputs");
                    $volumeFilter->setVolume($volume);
                    $chain->add($volumeFilter);
                }

                $inLabels[] = "aout$inputs";
                $inputs++;

                $graph->add($chain);
            }
        }

        $chain2 = new Filter\FilterChain(implode(',', $inLabels));

        $mix = Filter\Freeform::create('amix', ['inputs' => $inputs, 'duration' => 'first']);
        $chain2->add($mix);

        // since the volume is mixed with silence above, it makes the audio quieter than the editor preview.
        // so, multiply it by 2 here so it sounds like the original.
        $globalVol = new Filter\Volume();
        $globalVol->setVolume(2.0);
        $chain2->add($globalVol);

        $graph->add($chain2);

        return $graph;
    }

    public function generateVideoFilterGraph()
    {
        $graph = new Filter\FilterGraph();
        $this->sortItems();

        if ( $this->bgType == 'color' ) {
            $graph->add(FilterHelper::backgroundColor(
                $this->width, $this->height, $this->getDuration(), $this->bgColor, 'bg'));
        } else {
            $graph->addAll(FilterHelper::backgroundImage(
                $this->width, $this->height, $this->getDuration(), $this->bgImage, 'bg'));
        }

        $items = [];
        foreach ( $this->items as $index => $item ) {
            if ( ($item instanceof VideoItem || $item instanceof ImageItem) && !$item->getSource() ) {
                continue;
            }
            $items[] = $item;
        }

        $itemCount = count($items);
        $previousLabel = 'bg';
        $genCtr = 1;

        foreach ( $items as $index => $item )
        {
            $isFirstItem = $index === 0;
            $isLastItem = $index === $itemCount - 1;

            $rect = $item->getRect()->toArray();
            $timeStart = $item->getTimeStart();
            $timeEnd = $item->getTimeEnd();

            if ( $item instanceof VideoItem )
            {
                $label = $isLastItem ? '' : 'ge' . $genCtr++;

                $builder = FilterHelper::overlay($previousLabel, $label)
                    ->setVideo($item->getSource())
                    ->setRect($rect[0], $rect[1], $rect[2], $rect[3])
                    ->setTimeRange($timeStart, $timeEnd)
                    ->setStartTime($item->getSeekTo())
                    ->setEofAction($item->getEndBehavior());

                $previousLabel = $label;

                $graph->add($builder->buildSourceItem());
                $graph->add($builder->buildOverlayItem());
            }
            else if ( $item instanceof ImageItem )
            {
                $label = $isLastItem ? '' : 'ge' . $genCtr++;

                $builder = FilterHelper::overlay($previousLabel, $label)
                    ->setImage($item->getSource())
                    ->setRect($rect[0], $rect[1], $rect[2], $rect[3])
                    ->setTimeRange($timeStart, $timeEnd);

                $previousLabel = $label;

                $graph->add($builder->buildSourceItem());
                $graph->add($builder->buildOverlayItem());
            }
        }

        return $graph;
    }

    public function generateFilterGraph()
    {
        // this needs to be redone from scratch I think :( :( :(
        // basically, we need two separate filter graphs, one for audio, one for video.
        // then, use an ffmpeg command with two -i options, one for each, and combine into an .m4v.

        // the audio graph will be simpler, and will contain AudioSrc, Setpts, Volume, Amix, Apad filters.
        // http://ffmpeg.org/ffmpeg-filters.html#amix
        // http://ffmpeg.org/ffmpeg-filters.html#apad
        // Amix is kind of like Overlay for audio.
        // May need aevalsrc=0 for the base silence. http://ffmpeg.org/ffmpeg-filters.html#aevalsrc

        // see the Audio and Video-specific versions of this function

        trigger_error('do not use this', E_USER_ERROR);
    }

    public function getBackgroundType()
    {
        return $this->bgType;
    }

    public function getBackgroundImage()
    {
        return $this->bgImage;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
