<?php

namespace EzAd\Studio;

class FilterHelper
{
    /**
     * @param $width int Width of color source.
     * @param $height int Height of color source.
     * @param $duration int Duration in seconds.
     * @param $color int The color as an integer, like 0xFF0000 = red.
     * @param $label string The label of the source.
     */
    public static function backgroundColor($width, $height, $duration, $color, $label = 'bg')
    {
        $self = new Filter\ColorSrc('', $label);
        $self->setSize($width . 'x' . $height)
            ->setDuration($duration)
            ->setColor(Color::rgb($color));

        return $self;
    }

    public static function backgroundImage($width, $height, $duration, $image, $label = 'bg')
    {
        $bgColor = static::backgroundColor($width, $height, $duration, 0, '_bg1');

        $chain = new Filter\FilterChain('', '_bg2');

        $chain->add(Filter\MovieSrc::create($image), 'movie');
        $chain->add(Filter\Scale::create($width, $height), 'scale');
        $chain->add(Filter\Setpts::createZero(), 'setpts');

        $overlay = new Filter\Overlay('_bg1,_bg2', $label);

        return [$bgColor, $chain, $overlay];
    }

    public static function movieZero($file, $outLabel = '')
    {
        $movie  = Filter\MovieSrc::create($file);
        $setpts = Filter\Setpts::createZero();

        $chain = new Filter\FilterChain('', $outLabel);
        $chain->add($movie, 'movie');
        $chain->add($setpts, 'setpts');
        return $chain;
    }

    public static function movieOffset($file, $offset, $seekPoint = 0, $outLabel = '')
    {
        $movie = Filter\MovieSrc::create($file);
        //$movie->setSeekPoint($seekPoint);

        $setpts = Filter\Setpts::createOffset($offset - $seekPoint);

        $chain = new Filter\FilterChain('', $outLabel);
        $chain->add($movie, 'movie');
        $chain->add($setpts, 'setpts');
        return $chain;
    }

    public static function audioZero($file, $outLabel = '')
    {
        $audio = Filter\AudioSrc::create($file);
        $setpts = Filter\Setpts::createZero()->setIsAudio(true);

        $chain = new Filter\FilterChain('', $outLabel);
        $chain->add($audio, 'audio');
        $chain->add($setpts, 'setpts');
        return $chain;
    }

    public static function audioOffset($file, $offset, $seekPoint = 0, $outLabel = '')
    {
        $audio = Filter\AudioSrc::create($file);
        $setpts = Filter\Setpts::createOffset($offset - $seekPoint)->setIsAudio(true);

        $chain = new Filter\FilterChain('', $outLabel);
        $chain->add($audio, 'audio');
        $chain->add($setpts, 'setpts');
        return $chain;
    }

    public static function silence($duration, $label)
    {
        return Filter\Freeform::create('aevalsrc', ['exprs' => 0, 'd' => $duration], '', $label);
    }

    public static function overlay($in, $out = '')
    {
        $in = explode(',', $in);
        return new OverlayBuilder($in[0], isset($in[1]) ? $in[1] : '', $out);
    }
}
