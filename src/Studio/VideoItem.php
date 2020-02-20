<?php

namespace EzAd\Studio;

class VideoItem extends AbstractItem
{
    private $seekTo = 0;

    // pass = hide the video, pass through what's behind it
    // repeat = show the last frame of the video until the item ends
    private $endBehavior = 'pass';

    private $volume = 1.0;

    public function setSeekTo($seek)
    {
        $this->seekTo = $seek;
        return $this;
    }

    public function setEndBehavior($end)
    {
        $this->endBehavior = $end;
        return $this;
    }

    public function setVolume($volume)
    {
        $this->volume = $volume;
        return $this;
    }

    public function getSeekTo()
    {
        return $this->seekTo;
    }

    public function getEndBehavior()
    {
        return $this->endBehavior;
    }

    public function getVolume()
    {
        return $this->volume;
    }
}
