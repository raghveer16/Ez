<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Profile;
use EzAd\Bot\ProductStore\ElasticaStore;
use EzAd\Bot\ProductStore\ImageStorage;
use EzAd\Bot\ProductStore\Product;
use EzAd\Bot\Robot;
use EzAd\EZ;

/**
 * Class AbstractProfile
 * @package EzAd\Bot\Profile
 */
abstract class AbstractProfile implements ProfileInterface
{
    /**
     * {@inheritdoc}
     */
    abstract public function createNewRobot($domain);

    /**
     * {@inheritdoc}
     */
    public function saveRobotState(Robot $robot)
    {
        $data = $robot->saveState();
        $path = EZ::get('robot_state_path') . '/' . $robot->getDomain();
        file_put_contents($path, serialize($data));
    }

    /**
     * {@inheritdoc}
     */
    public function restoreRobotState(Robot $robot)
    {
        $data = file_get_contents(EZ::get('robot_state_path') . '/' . $robot->getDomain());
        $robot->restoreFromState(unserialize($data));
    }

    /**
     * {@inheritdoc}
     */
    public function isRestorable(Robot $robot)
    {
        return is_file(EZ::get('robot_state_path') . '/' . $robot->getDomain());
    }

    /**
     * {@inheritdoc}
     */
    public function handleNewProduct(Robot $robot, Product $product)
    {
        /** @var ImageStorage $imageStore */
        $imageStore = EZ::get('product_image_store');
        $imageStore->copyProductImages($product);

        /** @var ElasticaStore $productStore */
        $productStore = EZ::get('product_store');
        $productStore->saveProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function maybeRestore(Robot $robot)
    {
        if ( $this->isRestorable($robot) ) {
            $this->restoreRobotState($robot);
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldSkipPageUrl($url)
    {
        return false;
    }
}
