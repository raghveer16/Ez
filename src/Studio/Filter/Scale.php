<?php

namespace EzAd\Studio\Filter;

class Scale extends AbstractFilter
{
    const INTERL_FORCED = 1;
    const INTERL_NONE   = 0;
    const INTERL_AWARE  = -1;

    const ASPECT_DISABLE = 'disable';
    const ASPECT_DECREASE = 'decrease';
    const ASPECT_INCREASE = 'increase';

    public static function create($w, $h, $in = '', $out = '')
    {
        $self = new static($in, $out);
        $self->setWH($w, $h);
        return $self;
    }
    
    public function getName()
    {
        return 'scale';
    }

    public function setWidth($width)
    {
        return $this->set('w', $width);
    }

    public function setHeight($height)
    {
        return $this->set('h', $height);
    }

    public function setWH($w, $h)
    {
        return $this->setWidth($w)->setHeight($h);
    }

    public function setInterlacingMode($imode)
    {
        return $this->set('interl', $imode);
    }

    public function setAspectRatioType($type)
    {
        return $this->set('force_original_aspect_ratio', $type);
    }

    public function getWidth()
    {
        return $this->get('w');
    }

    public function getHeight()
    {
        return $this->get('h');
    }

    public function getInterlacingMode()
    {
        return $this->get('interl', self::INTERL_NONE);
    }

    public function getAspectRatioType()
    {
        return $this->get('force_original_aspect_ratio', self::ASPECT_DISABLE);
    }
}
