<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Extractor;
use EzAd\Bot\ProductStore\Product;
use EzAd\Commerce\Money;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class NewMediaRetailerExtractor
 * @package EzAd\Bot\Extractor
 */
class NewMediaRetailerExtractor implements ProductExtractorInterface
{
    /**
     * @param Crawler $crawler
     * @return Product
     */
    public function extractProductInfo(Crawler $crawler)
    {
        $product = new Product();

        $product->setTitle(trim($crawler->filter('h4.product_name')->text()));

        $image = $crawler->filter('img.img-centered');
        if ( $image->count() ) {
            $product->setImages([$image->attr('src')]);
        }

        $uniquePrices = [];

        $priceElements = $crawler->filter('span.rental_price');
        $priceElements->each(function($el) use ($product, &$uniquePrices) {
            /** @var Crawler $el */
            $duration = $el->filter('span.rental_duration')->text();
            $price = $el->filter('span.rental_price_amount')->text();
            $price = preg_replace('/[^0-9\.]/', '', trim($price));
            $duration = trim($duration, ": \n");

            // blame grandrentalmaine.net, their site is broken and has a duplicate product template
            if ( isset($uniquePrices[$duration]) ) {
                return;
            }
            $uniquePrices[$duration] = true;

            $product->addPrice(new Money($price), $duration);
        });

        // handle direct purchase price
        $directPurchase = $crawler->filter('span.purchase_price_amount');
        if ( $directPurchase->count() ) {
            $price = $directPurchase->first()->text();
            $price = preg_replace('/[^0-9\.]/', '', trim($price));
            $product->addPrice(new Money($price), 'Purchase');
        }

        return $product;
    }
}
