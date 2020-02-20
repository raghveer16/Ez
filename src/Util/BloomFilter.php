<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util;
use EzAd\Util\Collection\BitArray;

/**
 * Implementation of a bloom filter data structure that is backed by a BitArray. Doesn't do the
 * crazier hashing/bit-twiddling of other libraries, but this doesn't need to be incredibly fast,
 * just a good deal faster than disk access.
 *
 * Some code in put/maybeExists based on Guava Libraries, Copyright (C) 2011 The Guava Authors and
 * released under the Apache License version 2.0.
 * 
 * @package EzAd\Core\Util
 */
class BloomFilter implements \Serializable
{
    /**
     * @var BitArray
     */
    private $bitArray;

    private $hashFunctions;

    public function __construct($numBits, $expectedItems)
    {
        $this->bitArray = new BitArray($numBits);
        $this->hashFunctions = max(1, (int) round($numBits / $expectedItems * log(2)));
    }

    public function getHashFunctions()
    {
        return $this->hashFunctions;
    }

    public function put($value)
    {
        $h = $this->hash((string) $value);
        $hash1 = $h & 0xffffffff;
        $hash2 = ($h >> 32) & 0xffffffff;
        $len = $this->bitArray->getLength();

        for ( $i = 1; $i <= $this->hashFunctions; $i++ ) {
            $combinedHash = $hash1 + ($i * $hash2);
            // Flip all the bits if it's negative (guaranteed positive number)
            if ( $combinedHash < 0 ) {
                $combinedHash = ~$combinedHash;
            }
            $this->bitArray->set($combinedHash % $len);
        }
    }

    public function maybeExists($value)
    {
        $h = $this->hash((string) $value);
        $hash1 = $h & 0xffffffff;
        $hash2 = ($h >> 32) & 0xffffffff;
        $len = $this->bitArray->getLength();

        for ( $i = 1; $i <= $this->hashFunctions; $i++ ) {
            $combinedHash = $hash1 + ($i * $hash2);
            // Flip all the bits if it's negative (guaranteed positive number)
            if ( $combinedHash < 0 ) {
                $combinedHash = ~$combinedHash;
            }
            if ( !$this->bitArray->get($combinedHash % $len) ) {
                return false;
            }
        }

        return true;
    }

    private function hash($value)
    {
        $hash = hash('sha1', $value, true);
        $code = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $code |= ord($hash[$i]) << ($i * 8);
        }

        return $code;
    }

    public function serialize()
    {
        return serialize([
            'bits' => $this->bitArray,
            'hashFunctions' => $this->hashFunctions,
        ]);
    }

    public function unserialize($serialized)
    {
        $value = unserialize($serialized);
        $this->bitArray = $value['bits'];
        $this->hashFunctions = $value['hashFunctions'];
    }
}
