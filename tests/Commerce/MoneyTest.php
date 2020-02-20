<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Commerce;
use EzAd\Commerce\Money;

/**
 * Class MoneyTest
 * @package EzAdTests\Commerce
 */
class MoneyTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $money = new Money(1.0, 2, 'USD');

        $this->assertEquals('1.00', $money->getValue());
        $this->assertEquals(2, $money->getPrecision());
        $this->assertEquals('USD', $money->getCurrency());
    }

    public function testToString()
    {
        $money = new Money(1.0, 3);
        $this->assertEquals('1.000', (string) $money);
    }

    public function testCoercion()
    {
        $val1 = Money::coerce(1.0);
        $this->assertEquals('1.00', $val1->getValue());

        $val2 = Money::coerce(new Money(2.0));
        $this->assertEquals('2.00', $val2->getValue());
    }

    public function testOperations()
    {
        $value = new Money('2');
        $this->assertEquals('5.00', $value->add('3')->getValue());
        $this->assertEquals('-1.00', $value->sub(new Money('3'))->getValue());
        $this->assertEquals('6.00', $value->mul('3')->getValue());
        $this->assertEquals('0.50', $value->div('4')->getValue());
    }

    public function testIncompatiblePrecisions()
    {
        $val1 = new Money('1', 2);
        $val2 = new Money('1', 3);

        $this->setExpectedException('\InvalidArgumentException');
        $val1->add($val2);
    }

    public function testIncompatibleCurrencies()
    {
        $val1 = new Money('1', 2, 'USD');
        $val2 = new Money('1', 2, 'AUD');

        $this->setExpectedException('\InvalidArgumentException');
        $val1->add($val2);
    }

    public function testComparisions()
    {
        $val1 = new Money('2');
        $val2 = new Money('5');
        $val3 = new Money('5');

        $this->assertEquals(1, $val2->compare($val1));
        $this->assertEquals(0, $val2->compare($val3));
        $this->assertTrue($val2->equals($val3));
        $this->assertEquals(-1, $val1->compare($val2));

        $zero = new Money('0');
        $this->assertTrue($zero->isZero());
    }
}
