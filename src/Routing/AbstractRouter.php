<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Routing;

/**
 * Class AbstractRouter
 * @package EzAd\Routing
 */
class AbstractRouter implements RouterInterface
{
    /**
     * @var string
     */
    private $controllerPath;

    public function __construct($controllerPath)
    {
        $this->controllerPath = $controllerPath;
    }

    /**
     * @return string
     */
    public function getControllerPath()
    {
        return $this->controllerPath;
    }
}
