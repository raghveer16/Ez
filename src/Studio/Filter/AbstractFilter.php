<?php

namespace EzAd\Studio\Filter;

abstract class AbstractFilter extends AbstractGraphItem
{
    private $arguments = [];

    protected function set($key, $value)
    {
        if ( $value === null ) {
            $this->remove($key);
        } else {
            $this->arguments[$key] = $value;
        }
        return $this;
    }

    protected function remove($key)
    {
        unset($this->arguments[$key]);
    }

    protected function get($key, $default = null)
    {
        return $this->has($key) ? $this->arguments[$key] : $default;
    }

    protected function has($key)
    {
        return isset($this->arguments[$key]);
    }

    protected function all()
    {
        return $this->arguments;
    }

    /**
     * The expression can contain the following variables:
     *
     * 't'   - timestamp expressed in seconds, NAN if the input timestamp is unknown
     * 'n'   - sequential number of the input frame, starting from 0
     * 'pos' - the position in the file of the input frame, NAN if unknown
     * 'w'   - width of the input frame
     * 'h'   - height of the input frame
     */
    public function setEnableExpr($enable)
    {
        if ( $this->supportsTimeline() ) {
            $this->set('enable', $enable);
        }
        return $this;
    }

    public function setEnableRange($start, $end)
    {
        $start = (float) $start;
        $end = (float) $end;
        return $this->setEnableExpr("between(t,$start,$end)");
    }

    protected function supportsTimeline()
    {
        return true;
    }

    public function writeItem()
    {
        $args = $this->writeArgs();
        return EscapeUtils::description($this->getName() . ($args ? "=$args" : ''));
    }

    protected function writeArgs()
    {
        $s = [];
        foreach ( $this->arguments as $k => $v ) {
            $s[] = $k . '=' . EscapeUtils::option($v);
        }
        return implode(':', $s);
    }

    abstract public function getName();
}
