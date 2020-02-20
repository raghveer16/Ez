<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Extractor;
use EzAd\Bot\ProductStore\Product;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Interface ProductExtractorInterface
 * @package EzAd\Bot\Extractor
 */
interface ProductExtractorInterface
{
    /**
     * @param Crawler $crawler
     * @return Product
     */
    public function extractProductInfo(Crawler $crawler);
}
