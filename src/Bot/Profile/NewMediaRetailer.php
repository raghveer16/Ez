<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Profile;
use EzAd\Bot\Category\CategoryRobot;
use EzAd\Bot\Category\CategoryUtils;
use EzAd\Bot\Category\DatabaseStorage;
use EzAd\Bot\Category\Loader\NewMediaRetailerLoader;
use EzAd\Bot\Extractor\NewMediaRetailerExtractor;
use EzAd\Bot\ProductStore\ElasticaStore;
use EzAd\Bot\ProductStore\ImageStorage;
use EzAd\Bot\ProductStore\Product;
use EzAd\Bot\Robot;
use EzAd\EZ;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Class NewMediaRetailer
 * @package EzAd\Bot\Profile
 */
class NewMediaRetailer extends AbstractProfile
{
    /**
     * Creates a new robot and configures it.
     *
     * @param string $domain
     * @return Robot
     */
    public function createNewRobot($domain)
    {
        $db = EZ::get('database');
        $catStorage = new DatabaseStorage($db);
        $catLoader = new NewMediaRetailerLoader($domain, $catStorage);
        $catLoader->syncAndFlush();

        $categories = $catStorage->getCategories($domain);
        $root = CategoryUtils::getRoots($categories);

        $robot = new CategoryRobot($domain, new NewMediaRetailerExtractor(), $root);
        $robot->setLogger(new Logger('robot', [new StreamHandler('php://stdout')]));
        $robot->setProductAnchorSelector('h4.product_name > a');
        $robot->setPageUrlSelector('div.pagination_wrap > ul > li > a');
        $robot->setLeafCategoriesOnly(false);
        $robot->setRobotProfile($this);
        return $robot;
    }

    public function handleNewProduct(Robot $robot, Product $product)
    {
        /** @var ImageStorage $imageStore */
        //$imageStore = EZ::get('product_image_store');
        //$imageStore->copyProductImages($product);

        /** @var ElasticaStore $productStore */
        $productStore = EZ::get('product_store');
        $productStore->saveProduct($product);
    }

    public function shouldSkipPageUrl($url)
    {
        return preg_match('#/page1$#', $url);
    }
}
