<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;
use EzAd\Address\Address;
use EzAd\Commerce\Money;

/**
 * Basic invoice representation that can be transformed/sent via different handlers (EDI for now).
 *
 * @package EzAd\Commerce\Invoicing
 */
class Invoice
{
    /**
     * @var int
     */
    private $invoiceNumber;

    /**
     * @var \DateTime
     */
    private $invoiceDate;

    /**
     * @var string
     */
    private $businessName;

    /**
     * @var \DateTime
     */
    private $dueDate;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var string
     */
    private $storeNumber;

    /**
     * @var string
     */
    private $poNumber;

    /**
     * @var LineItem[]
     */
    private $lineItems;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @param int $number The invoice number.
     * @param string $business The business name.
     * @param \DateTime $date Date of the invoice.
     * @param int|\DateTime $due Either the # of days until due, or a specific date.
     * @param Address $address The address of the business.
     * @param LineItem[] $items Line items of the invoice.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($number, $business, \DateTime $date, $due, Address $address, array $items = [])
    {
        $this->invoiceNumber = $number;
        $this->businessName = $business;
        $this->invoiceDate = $date;

        if ( is_int($due) ) {
            $dueDate = clone $this->invoiceDate;
            $dueDate->modify("+$due days");
            $due = $dueDate;
        }
        if ( !$due instanceof \DateTime ) {
            throw new \InvalidArgumentException('$due must be an integer (# of days) or a DateTime instance');
        }

        $this->dueDate = $due;
        $this->address = $address;
        $this->lineItems = $items;
    }

    /**
     * Gets the sum of all line item totals.
     *
     * @return Money
     */
    public function getTotal()
    {
        if ( empty($this->lineItems) ) {
            return new Money(0);
        }

        $sum = $this->lineItems[0]->getLineTotal();
        for ( $i = 1; $i < count($this->lineItems); $i++ ) {
            $sum = $sum->add($this->lineItems[$i]->getLineTotal());
        }

        return $sum;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getBusinessName()
    {
        return $this->businessName;
    }

    /**
     * @param string $businessName
     */
    public function setBusinessName($businessName)
    {
        $this->businessName = $businessName;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return \DateTime
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * @param \DateTime $invoiceDate
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;
    }

    /**
     * @return int
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @param int $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @return LineItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @param LineItem[] $lineItems
     */
    public function setLineItems($lineItems)
    {
        $this->lineItems = $lineItems;
    }

    /**
     * @param LineItem $lineItem
     */
    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems[] = $lineItem;
    }

    /**
     * @return string
     */
    public function getStoreNumber()
    {
        return $this->storeNumber;
    }

    /**
     * @param string $storeNumber
     */
    public function setStoreNumber($storeNumber)
    {
        $this->storeNumber = $storeNumber;
    }

    /**
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * @param string $poNumber
     */
    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;
    }

    /**
     * @param string|array $key
     * @param mixed $value
     */
    public function setMeta($key, $value = null)
    {
        if ( $value === null ) {
            if ( is_array($key) ) {
                foreach ( $key as $k => $v ) {
                    $this->setMeta($k, $v);
                }
            } else if ( isset($this->metadata[$key]) ) {
                unset($this->metadata[$key]);
            }
            return;
        }

        $this->metadata[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function getMeta($key = null)
    {
        if ( $key === null ) {
            return $this->metadata;
        }
        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }
}
