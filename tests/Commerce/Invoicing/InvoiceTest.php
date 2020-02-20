<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Commerce\Invoicing;
use EzAd\Address\Address;
use EzAd\Commerce\Invoicing\Invoice;
use EzAd\Commerce\Invoicing\LineItem;
use EzAd\Commerce\Money;

/**
 * Class InvoiceTest
 * @package EzAdTests\Commerce\Invoicing
 */
class InvoiceTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $date = new \DateTime('2014-07-07');
        $due = new \DateTime('2014-07-14');
        $invoice = new Invoice(100, 'Test Business', $date, $due, new Address());

        $this->assertEquals(100, $invoice->getInvoiceNumber());
        $this->assertEquals('Test Business', $invoice->getBusinessName());
        $this->assertEquals($date, $invoice->getInvoiceDate());
        $this->assertEquals($due, $invoice->getDueDate());
    }

    public function testNumericDueParameter()
    {
        $invoice = new Invoice(101, 'Test', date_create('2014-07-07'), 10, new Address());
        $this->assertEquals('2014-07-17', $invoice->getDueDate()->format('Y-m-d'));
    }

    public function testCalculations()
    {
        $invoice = new Invoice(102, 'Test', date_create('now'), 10, new Address());

        $invoice->addLineItem(new LineItem('111', 'Item 1', '4.99', 3));
        $invoice->addLineItem(new LineItem('222', 'Item 2', '9.95', 1));
        $invoice->addLineItem(new LineItem('333', 'Item 3', '19.99', 2));
        $invoice->addLineItem(new LineItem('444', 'Credit', '-5.00', 1));

        $this->assertEquals('59.90', $invoice->getTotal()->getValue());
    }

    public function testIncompatibleMoneyTypes()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $invoice = new Invoice(103, 'Test', date_create('now'), 10, new Address());
        $invoice->addLineItem(new LineItem('111', 'Item 1', new Money('4.99', 2, 'USD'), 1));
        $invoice->addLineItem(new LineItem('222', 'Item 2', new Money('3.50', 2, 'CAN'), 1));

        $invoice->getTotal();
    }
}
