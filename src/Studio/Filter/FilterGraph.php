<?php

namespace EzAd\Studio\Filter;

class FilterGraph
{
    private $items = [];

    public function __construct(array $items = [])
    {
        $this->addAll($items);
    }

    public function add(AbstractGraphItem $item)
    {
        $this->items[] = $item;
        return $this;
    }

    public function addAll(array $items)
    {
        foreach ( $items as $item ) {
            $this->add($item);
        }
        return $this;
    }

    public function toString()
    {
        if ( empty($this->items) ) {
            return '';
        }

        $s = [];
        foreach ( $this->items as $item ) {
            if ( $item instanceof FilterChain ) {
                $s[] = $item->toString();
            } else {
                $s[] = $item->toString();//EscapeUtils::description($item->toString());
            }
        }

        return EscapeUtils::graph(implode('; ', $s));
    }

    public function __toString()
    {
        return $this->toString();
    }
}
