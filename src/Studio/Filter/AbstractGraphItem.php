<?php

namespace EzAd\Studio\Filter;

abstract class AbstractGraphItem
{
    protected $inputs = [];
    protected $outputs = [];

    public function __construct($inputs = '', $outputs = '')
    {
        $inputs = trim($inputs);
        $outputs = trim($outputs);

        if ( $inputs ) {
            $this->inputs = array_filter(array_map('trim', explode(',', $inputs)));
        }
        if ( $outputs ) {
            $this->outputs = array_filter(array_map('trim', explode(',', $outputs)));
        }

        $this->setup();
    }

    public function setup()
    {

    }

    public function toString()
    {
        $itemString = $this->writeItem();
        if ( empty($itemString) ) {
            return '';
        }

        $s  = $this->writeLabels($this->inputs);
        $s .= $itemString;
        $s .= $this->writeLabels($this->outputs);
        return $s;
    }

    private function writeLabels(array $labels)
    {
        if ( empty($labels) ) {
            return '';
        }

        return '[' . implode('][', $labels) . ']';
    }

    abstract protected function writeItem();
}
