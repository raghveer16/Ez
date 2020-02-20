<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\ProductStore;

/**
 * Interface for product storage systems. Will most likely just include SQL DB and ElasticSearch via Elastica.
 *
 * @package EzAd\Bot\ProductStore
 */
interface ProductStoreInterface
{
    /**
     * Saves the product information into storage. Returns a unique identifier for the added product.
     *
     * @param Product $product
     * @return string
     */
    public function saveProduct(Product $product);
    
    /**
     * Finds a single product by a given ID.
     *
     * @param string $id
     * @return Product
     */
    public function findById($id);

    /**
     * @param $domain
     * @param $title
     * @return Product
     */
    public function findByExactTitle($domain, $title);

    /**
     * Finds products for a domain in the given category.
     *
     * @param $domain
     * @param $category
     * @param $offset
     * @param $limit
     * @return array First element is Product[], second is total
     */
    public function findByCategory($domain, $category, $offset, $limit);

    /**
     * Finds a product for the given SKU number.
     *
     * @param $domain
     * @param $sku
     * @return Product
     */
    public function findBySku($domain, $sku);

    /**
     * Finds a product for the given UPC number.
     *
     * @param $domain
     * @param $upc
     * @return Product
     */
    public function findByUpc($domain, $upc);

    /**
     * Searches across product title, SKU, and UPC to find products.
     *
     * @param $domain
     * @param $search
     * @param $offset
     * @param $limit
     * @return array First element is Product[], second is total
     */
    public function search($domain, $search, $offset, $limit);
}
