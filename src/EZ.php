<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd;
use Pimple\Container;

/**
 * Class EZ
 * @package EzAd
 */
class EZ
{
    /**
     * @var Container
     */
    private static $container;

    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    public function getContainer()
    {
        return self::$container;
    }

    public static function get($key)
    {
        return self::$container[$key];
    }
}
