<?php

namespace EzAd\Studio;

class OverlayBuilder
{
    private $input1;
    private $input2;
    private $output;

    private $type;
    private $filePath;

    private $overlayRect = [0, 0, 0, 0];
    private $overlayTimeRange = [0, 0];
    private $videoStartTime = 0;
    private $volume = 1.0;

    private $eofAction = Filter\Overlay::EOF_ACTION_REPEAT;

    private static $shortUniq = 1;

    public function __construct($input1, $input2, $output)
    {
        $this->input1 = $input1;
        $this->input2 = $input2 ?: 't' . self::$shortUniq++;
        $this->output = $output;
    }

    public function setImage($file)
    {
        $this->type = 'image';
        $this->filePath = $file;
        return $this;
    }

    public function setVideo($file)
    {
        $this->type = 'video';
        $this->filePath = $file;
        return $this;
    }

    public function setRect($x, $y, $w, $h)
    {
        $this->overlayRect = [$x, $y, $w, $h];
        return $this;
    }

    public function setTimeRange($start, $end)
    {
        $this->overlayTimeRange = [$start, $end];
        return $this;
    }

    public function setStartTime($start)
    {
        $this->videoStartTime = $start;
        return $this;
    }

    public function setEofPass()
    {
        return $this->setEofAction(Filter\Overlay::EOF_ACTION_PASS);
    }

    public function setEofEndAll()
    {
        return $this->setEofAction(Filter\Overlay::EOF_ACTION_ENDALL);
    }

    public function setEofAction($action)
    {
        if ( in_array($action, [Filter\Overlay::EOF_ACTION_PASS, Filter\Overlay::EOF_ACTION_REPEAT,
            Filter\Overlay::EOF_ACTION_ENDALL]) ) {
            $this->eofAction = $action;
        }

        return $this;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;
        return $this;
    }

    public function buildSourceItem()
    {
        if ( $this->type == 'video' ) {
            if ( $this->videoStartTime == 0 && $this->overlayTimeRange[0] == 0 ) {
                $chain = FilterHelper::movieZero($this->filePath, $this->input2);
            } else {
                $chain = FilterHelper::movieOffset(
                    $this->filePath, $this->overlayTimeRange[0], $this->videoStartTime, $this->input2);
            }

            $chain->add(Filter\Scale::create($this->overlayRect[2], $this->overlayRect[3]), 'scale');
            
            return $chain;
        } else if ( $this->type == 'image' ) {
            $chain = FilterHelper::movieZero($this->filePath, $this->input2);
            $chain->add(Filter\Scale::create($this->overlayRect[2], $this->overlayRect[3]), 'scale');
            return $chain;
        }

        return false;
    }

    public function buildOverlayItem()
    {
        $overlay = new Filter\Overlay($this->input1 . ',' . $this->input2, $this->output);
        $overlay->setEnableRange($this->overlayTimeRange[0], $this->overlayTimeRange[1])
            ->setXY($this->overlayRect[0], $this->overlayRect[1])
            ->setEofAction($this->eofAction);

        return $overlay;
    }
}
