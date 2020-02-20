<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAdTests\Commerce\Invoicing;
use EzAd\Address\Address;
use EzAd\Commerce\Invoicing\EDITransformer;
use EzAd\Commerce\Invoicing\Invoice;
use EzAd\Commerce\Invoicing\LineItem;
use EzAd\Commerce\Money;

/**
 * Class EDITransformerTest
 * @package EzAdTests\Commerce\Invoicing
 */
class EDITransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testLineItemTransform()
    {
        $tr = new EDITransformer();
        $item = new LineItem('12345', 'Test Item', '4.99', 3);

        // sku, truevalue #, upc #, gtin #, qty, unit, price, description
        // 12345    (T.V. #)    (UPC)   (GTIN)  3   EA  4.99    Test Item
        $this->assertEquals("12345\t\t\t\t3\tEA\t4.99\tTest Item", $tr->encodeLineItem($item));
    }

    public function testInvoiceTransform()
    {
        $tr = new EDITransformer();

        $address = new Address();
        $address->setName('Steven Harris');
        $address->setStreetLines(['333 Test Street', 'Apt 201']);
        $address->setCity('Clinton Twp');
        $address->setAdminArea('MI');
        $address->setPostalCode('48035');

        $invoice = new Invoice(101, 'Test Business', date_create('2014-07-07'), 10, $address);
        $invoice->setStoreNumber('12345');
        $invoice->addLineItem(new LineItem('111', 'Test Item', '9.99', 2));
        $invoice->addLineItem(new LineItem('222', 'Credit', '-5.00', 1));

        $this->assertEquals("101\t07/07/2014\t101\t07/07/2014\t07/07/2014\t\t\t\tF670\t-5.00\tDISCOUNT\t14.98\t\t012345"
            . "\tSteven Harris\t333 Test Street\tClinton Twp\tMI\t48035\t\t\t07/17/2014\t\t",
            $tr->encodeInvoice($invoice, new Money('-5.00')));
    }

    public function testFullInvoiceTransform()
    {
        $tr = new EDITransformer();

        $address = new Address();
        $address->setName('Steven Harris');
        $address->setStreetLines(['333 Test Street', 'Apt 201']);
        $address->setCity('Clinton Twp');
        $address->setAdminArea('MI');
        $address->setPostalCode('48035');

        $invoice = new Invoice(101, 'Test Business', date_create('2014-07-07'), 10, $address);
        $invoice->setStoreNumber('12345');
        $invoice->addLineItem(new LineItem('111', 'Test Item', '9.99', 2));
        $invoice->addLineItem(new LineItem('123', 'Test 2', '14.95', 1));
        $invoice->addLineItem(new LineItem('222', 'Credit', '-5.00', 1));

        $expected = file_get_contents(__DIR__ . '/data/invtest.csv');
        $this->assertEquals($expected, $tr->transform([$invoice]));
    }
}
