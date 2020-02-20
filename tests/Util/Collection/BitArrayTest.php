<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Util\Collection;

use EzAd\Util\Collection\BitArray;

class BitArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testLength()
    {
        $ba = new BitArray(32);
        $this->assertEquals(32, $ba->getLength());
        $this->assertEquals(4, $ba->getByteLength());

        $ba2 = new BitArray(293722);
        $this->assertEquals(36716, $ba2->getByteLength());
    }

    public function testOutOfBounds()
    {
        $ba = new BitArray(32);
        $this->setExpectedException('\PHPUnit_Framework_Error_Notice');
        $ba->get(32);
    }

    public function testSetAndGet()
    {
        $ba = new BitArray(32);

        $ba->set(0);
        $this->assertTrue($ba->get(0));

        $ba->set(0, false);
        $this->assertFalse($ba->get(0));

        for ( $i = 0; $i < 32; $i++ ) {
            $ba->set($i);
        }

        $ba->set(18, false);
        $this->assertFalse($ba->get(18));
        $this->assertTrue($ba->get(19));
    }

    public function testSerialize()
    {
        $ba = new BitArray(32);
        $ba->set(11);
        $ser = serialize($ba);

        $ba2 = unserialize($ser);
        $this->assertEquals(32, $ba2->getLength());
        $this->assertTrue($ba2->get(11));
    }
}
