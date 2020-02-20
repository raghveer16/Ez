<?php

namespace EzAd\Studio;

use EzAd\Util\Geometry\Rect;
use EzAd\Util\ArgMatcher;

abstract class AbstractItem
{
    protected $source = '';

    protected $rect;

    protected $angle = 0;

    protected $layerIndex = 0;

    protected $timeStart = 0;

    protected $timeEnd = 0;

    public function __construct()
    {
        $this->rect = Rect::zero();
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getRect()
    {
        return $this->rect;
    }

    public function getAngle()
    {
        return $this->angle;
    }

    public function getLayerIndex()
    {
        return $this->layerIndex;
    }

    public function getTimeStart()
    {
        return $this->timeStart;
    }

    public function getTimeEnd()
    {
        return $this->timeEnd;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function setRect()
    {
        $args = new ArgMatcher(func_get_args());

        if ( $args->matches('nnnn') ) {
            $rect = Rect::fromArray($args->get());
        } else if ( $args->matches('a') && count($args[0]) == 4 ) {
            $rect = Rect::fromArray($args[0]);
        } else if ( $args->matches('o', Rect::class) ) {
            $rect = $args[0];
        } else {
            throw new \InvalidArgumentException('Arguments did not match nnnn, a, or o(Rect)');
        }

        $this->rect = $rect;

        return $this;
    }

    public function setAngle($angle)
    {
        $this->angle = $angle;
        return $this;
    }

    public function setLayerIndex($layerIndex)
    {
        $this->layerIndex = $layerIndex;
        return $this;
    }

    public function setTimeRange($start, $end)
    {
        $this->timeStart = $start;
        $this->timeEnd = $end;
        return $this;
    }

    public function setTimeStart($start)
    {
        $this->timeStart = $start;
        return $this;
    }

    public function setTimeEnd($end)
    {
        $this->timeEnd = $end;
        return $this;
    }
}
