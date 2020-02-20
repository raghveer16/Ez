<?php

namespace EzAd\Studio\Filter;

class Setpts extends AbstractFilter
{
    public $isAudio = false;

    public static function create($expr, $in = '', $out = '')
    {
        $self = new static($in, $out);
        $self->setExpr($expr);
        return $self;
    }

    public static function createZero($in = '', $out = '')
    {
        return static::create('PTS-STARTPTS', $in, $out);
    }

    public static function createOffset($offset, $in = '', $out = '')
    {
        $op = $offset >= 0 ? '+' : '-';
        $offset = abs($offset);
        return static::create('PTS'.$op.$offset.'/TB', $in, $out);
    }

    public function setIsAudio($isAudio)
    {
        $this->isAudio = $isAudio;
        return $this;
    }

    public function getName()
    {
        return $this->isAudio ? 'asetpts' : 'setpts';
    }

    public function setExpr($expr)
    {
        return $this->set('expr', $expr);
    }

    public function getExpr()
    {
        return $this->get('expr');
    }
}
