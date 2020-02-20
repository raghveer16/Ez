<?php

namespace EzAd\Studio\Filter;

class Freeform extends AbstractFilter
{
    private $name;

    public static function create($name, array $properties = [], $in = '', $out = '')
    {
        $self = new static($in, $out);
        $self->name = $name;

        if ( $properties ) {
            $self->setProperties($properties);
        }

        return $self;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setProperties(array $props)
    {
        foreach ( $props as $k => $v ) {
            $this->setProp($k, $v);
        }
        return $this;
    }

    public function setProp($key, $value)
    {
        return $this->set($key, $value);
    }

    public function getProp($key, $default)
    {
        return $this->get($key, $default);
    }
}
