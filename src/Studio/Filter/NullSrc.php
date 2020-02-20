<?php

namespace EzAd\Studio\Filter;

/**
 * Null video source. Is very weird with overlays, so don't use this. Instead use the ColorSrc
 * as the base of the video canvas.
 */
class NullSrc extends AbstractFilter
{
    public function getName()
    {
        return 'nullsrc';
    }

    public function setDuration($duration)
    {
        return $this->set('duration', $duration);
    }

    public function setSize($size)
    {
        return $this->set('size', $size === null ? $size : (string)$size);
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
