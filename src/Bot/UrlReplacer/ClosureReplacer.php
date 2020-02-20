<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlReplacer;

/**
 * Class ClosureReplacer
 * @package EzAd\Bot\UrlReplacer
 */
class ClosureReplacer implements UrlReplacerInterface
{
    private $closure;

    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param string $url
     * @return string
     */
    public function replace($url)
    {
        $c =  $this->closure;
        return $c($url);
    }
}
