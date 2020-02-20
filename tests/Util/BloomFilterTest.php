<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Util;
use EzAd\Util\BloomFilter;

/**
 * Class BloomFilterTest
 * @package EzAdTests\Core\Util
 */
class BloomFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider bloomProvider
     */
    public function testSetAndExists($value)
    {
        $bf = new BloomFilter(1 << 20, 1 << 16);
        $bf->put($value);
        $this->assertTrue($bf->maybeExists($value));
    }

    public function testNotExists()
    {
        $bf = new BloomFilter(1 << 10, 1 << 6);
        $bf->put('Something');
        $this->assertFalse($bf->maybeExists('Hopefully not'));
    }

    public function testSerialize()
    {
        $bf = new BloomFilter(1 << 10, 1 << 6);
        $bf->put('Something');
        $serialized = serialize($bf);

        $bf2 = unserialize($serialized);
        $this->assertTrue($bf2->maybeExists('Something'));
    }

    public function bloomProvider()
    {
        return [
            ['Hello World'],
            ['Blah asdf'],
            ['http://www.truevalue.com/product/Outdoor-Living-Patio-Furniture/Grills-Outdoor-Cooking/Charcoal-Grills/'
                . 'One-Touch-Gold-Charcoal-Grill-Copper-225-In/pc/12/c/178/sc/1579/56040.uts']
        ];
    }
}
