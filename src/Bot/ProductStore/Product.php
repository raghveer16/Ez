<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\ProductStore;
use EzAd\Commerce\Money;

/**
 * Represents a product that can be stored and searched.
 *
 * @package EzAd\Bot\ProductStore
 */
class Product
{
    /**
     * Internal ID of the product.
     *
     * @var string
     */
    private $id = '';

    /**
     * Title of the product.
     *
     * @var string
     */
    private $title = '';

    /**
     * Domain of the website associated with this item.
     *
     * @var string
     */
    private $domain = '';

    /**
     * URL where product can be found.
     *
     * @var string
     */
    private $url = '';

    /**
     * SKU number.
     *
     * @var string
     */
    private $sku = '';

    /**
     * UPC number.
     *
     * @var string
     */
    private $upc = '';

    /**
     * Product images.
     *
     * @var array
     */
    private $images = [];

    /**
     * Product prices. Supports multiple prices for things like rentals with multiple periods.
     *
     * @var array
     */
    private $prices = [];

    /**
     * List of category IDs the product is a member of, from least to most specific.
     *
     * @var array
     */
    private $categories = [];

    /**
     * Date the product was added.
     *
     * @var \DateTime
     */
    private $dateAdded;

    /**
     * Date of the product's last update.
     *
     * @var \DateTime
     */
    private $dateModified;

    public function __construct()
    {
        $this->dateAdded = new \DateTime();
        $this->dateModified = new \DateTime();
    }

    public function addPrice(Money $price, $category = 'Main')
    {
        $prec = $price->getPrecision();
        if ( $prec == 0 ) {
            $amount = 100 * (int) $price->getValue();
        } else if ( $prec <= 2 ) {
            $amount = (int) $price->mul(100)->getValue();
        } else {
            $amount = (int) round((float) $price->mul(100)->getValue());
        }

        $this->prices[] = ['amount' => $amount, 'category' => $category, 'currency' => $price->getCurrency()];
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;
    }

    /**
     * @return \DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param \DateTime $dateModified
     */
    public function setDateModified($dateModified)
    {
        $this->dateModified = $dateModified;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param array $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return array
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param array $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * @param string $upc
     */
    public function setUpc($upc)
    {
        $this->upc = $upc;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}
