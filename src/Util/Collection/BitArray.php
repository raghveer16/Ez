<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util\Collection;

/**
 * Stores an immutable-length sequence of bits, backed by a binary string.
 *
 * @package EzAd\Core\Util\Collection
 */
class BitArray implements \Serializable
{
    private $data = '';
    private $length = 0;

    /**
     * @param int $length Number of bits. Allocates a string ceil(length/8) bytes long.
     */
    public function __construct($length)
    {
        $this->length = $length;
        $this->data = str_repeat("\0", ceil($length / 8));
    }

    public function set($pos, $value = true)
    {
        $index = (int) ($pos / 8);
        $offset = $pos % 8;
        $byte = ord($this->data[$index]);

        if ( $value ) {
            //$changed = ($byte & (1 << $offset)) === 0;
            $byte = $byte | (1 << $offset);
        } else {
            //$changed = ($byte & (1 << $offset)) !== 0;
            $byte = ($byte & ~(1 << $offset)) & 0xff;
        }

        $this->data[$index] = chr($byte);
        //return $changed;
    }

    public function get($pos)
    {
        $index = (int) ($pos / 8);
        $offset = $pos % 8;

        $byte = ord($this->data[$index]);
        return ($byte & (1 << $offset)) != 0;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getByteLength()
    {
        return strlen($this->data);
    }

    public function serialize()
    {
        return serialize([
            'length' => $this->length,
            'data' => base64_encode($this->data),
        ]);
    }

    public function unserialize($serialized)
    {
        $value = unserialize($serialized);
        $this->length = $value['length'];
        $this->data = base64_decode($value['data']);
    }
}
