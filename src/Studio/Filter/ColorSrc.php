<?php

namespace EzAd\Studio\Filter;

class ColorSrc extends AbstractFilter
{
    // keep in mind for using video sources as input:
    // ffmpeg -f lavfi -i "color=c=red:d=10:size=hd720" red10.m4v
    //
    // $color = new ColorSrc();
    // $color->color = 'red';
    // $color->duration = 10;
    // $color->size = VideoConstants::SIZE_HD720;
    // $cmd = 'ffmpeg -f lavfi -i "' . $color->writeItem() . '" red10.m4v';
    public function getName()
    {
        return 'color';
    }

    public function setColor($color)
    {
        return $this->set('color', $color);
    }

    public function setDuration($duration)
    {
        return $this->set('duration', $duration);
    }

    public function setSize($size)
    {
        return $this->set('size', $size === null ? $size : (string)$size);
    }

    public function getColor()
    {
        return $this->get('color');
    }

    public function getDuration()
    {
        return $this->get('duration');
    }

    public function getSize()
    {
        return $this->get('size');
    }
}
