<?php

namespace EzAd\Studio\Filter;

class FilterChain extends AbstractGraphItem
{
    private $filters = [];

    public static function create(array $items, $in = '', $out = '')
    {
        $chain = new static($in, $out);
        $chain->addAll($items);
        return $chain;
    }

    public function add(AbstractFilter $filter, $name = '')
    {
        if ( $name ) {
            $this->filters[$name] = $filter;
        } else {
            $this->filters[] = $filter;
        }
        return $this;
    }

    public function get($name)
    {
        return isset($this->filters[$name]) ? $this->filters[$name] : null;
    }

    public function find($className)
    {
        foreach ( $this->filters as $filter ) {
            if ( $filter instanceof $className ) {
                return $filter;
            }
        }
        return null;
    }

    public function addAll(array $items)
    {
        foreach ( $items as $item ) {
            $this->add($item);
        }
        return $this;
    }

    protected function writeItem()
    {
        if ( empty($this->filters) ) {
            return '';
        }

        $s = [];
        foreach ( $this->filters as $filter ) {
            $s[] = $filter->toString();//EscapeUtils::description($filter->toString());
        }

        return implode(', ', $s);
    }
}
