<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Profile;
use EzAd\Bot\ProductStore\Product;
use EzAd\Bot\Robot;

/**
 * A profile defines how a robot is created, saved, and restored. It knows what robot to use,
 * the category loader, product extractor, and any other relevant options.
 *
 * You'll generally want to extend AbstractProfile to reduce code... once I make it.
 *
 * @package EzAd\Bot\Profile
 */
interface ProfileInterface
{
    /**
     * Creates a new robot and configures it.
     *
     * @param string $domain
     * @return Robot
     */
    public function createNewRobot($domain);

    /**
     * Persists a robot's internal state.
     *
     * @param Robot $robot
     */
    public function saveRobotState(Robot $robot);

    /**
     * Restores a robot's state.
     *
     * @param Robot $robot
     */
    public function restoreRobotState(Robot $robot);

    /**
     * Checks if this robot is in-progress (has a saved state).
     *
     * @param Robot $robot
     * @return bool
     */
    public function isRestorable(Robot $robot);

    /**
     * Handles a discovered product from the robot.
     *
     * @param Robot $robot
     * @param Product $product
     */
    public function handleNewProduct(Robot $robot, Product $product);

    /**
     * Restores the robot's state if a state file exists.
     *
     * @param Robot $robot
     * @return bool True if state was restored.
     */
    public function maybeRestore(Robot $robot);

    /**
     * Return true to skip this URL when going through pagination. Used in GRS for example, to skip /page1.
     *
     * @param $url
     * @return bool
     */
    public function shouldSkipPageUrl($url);
}
