<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;
use EzAd\Commerce\Money;

/**
 * Representation of a line item on an invoice.
 *
 * @package EzAd\Commerce\Invoicing
 */
class LineItem
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Money
     */
    private $price;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param $sku
     * @param $description
     * @param $price
     * @param int $quantity
     */
    public function __construct($sku, $description, $price, $quantity = 1)
    {
        $this->sku = $sku;
        $this->description = $description;
        $this->price = Money::coerce($price);
        $this->quantity = $quantity;
    }

    /**
     * Gets the price * quantity for this line item.
     *
     * @return Money
     */
    public function getLineTotal()
    {
        return $this->price->mul($this->quantity);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Money
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @param int $precision
     * @param string $currency
     */
    public function setPrice($price, $precision = 2, $currency = 'USD')
    {
        $this->price = Money::coerce($price, $precision, $currency);
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }
}
