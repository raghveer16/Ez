<?php

namespace EzAd\Util;

class ArgMatcher implements \ArrayAccess
{
    private $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    // $args->matches('iiii')
    // $args->matches('a')
    // $args->matches('o', Rect::class)
    public function matches($spec /*, object names*/)
    {
        $objects = array_slice(func_get_args(), 1);

        $args = $this->args;
        $nargs = count($args);
        $len = strlen($spec);
        
        if ( $nargs !== $len ) {
            return false;
        }

        $objIndex = 0;
        for ( $i = 0; $i < $len; $i++ ) {
            $type = $spec[$i];
            if ( $type === 'i' && !is_int($args[$i]) ) {
                return false;
            } else if ( $type === 'f' && !is_float($args[$i]) ) {
                return false;
            } else if ( $type === 'n' && !is_numeric($args[$i]) ) {
                return false;
            } else if ( $type === 's' && !is_string($args[$i]) ) {
                return false;
            } else if ( $type === 'a' && !is_array($args[$i]) ) {
                return false;
            } else if ( $type === 'o' ) {
                $check = $objects[$objIndex];
                $objIndex++;
                if ( !is_object($args[$i]) || !$args[$i] instanceof $check ) {
                    return false;
                }
            }
        }

        return true;
    }

    public function get()
    {
        return $this->args;
    }

    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}

    public function offsetExists($offset)
    {
        return isset($this->args[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->args[$offset]) ? $this->args[$offset] : null;
    }
}
