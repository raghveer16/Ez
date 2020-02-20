<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Commerce\Invoicing;
use EzAd\Commerce\Invoicing\LineItem;
use EzAd\Commerce\Money;

/**
 * Class LineItemTest
 * @package EzAdTests\Commerce\Invoicing
 */
class LineItemTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $item = new LineItem('123abc', 'Test Item', new Money('5.00'), 1);

        $this->assertEquals('123abc', $item->getSku());
        $this->assertEquals('Test Item', $item->getDescription());
        $this->assertEquals('5.00', $item->getPrice()->getValue());
        $this->assertEquals(1, $item->getQuantity());
    }

    public function testLineItemTotal()
    {
        $item = new LineItem('10203', 'Test Item', new Money('9.99'), 4);
        $this->assertEquals('39.96', $item->getLineTotal()->getValue());
    }
}
