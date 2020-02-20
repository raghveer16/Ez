<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlReplacer;

/**
 * Interface UrlReplacerInterface
 * @package EzAd\Bot\UrlReplacer
 */
interface UrlReplacerInterface
{
    /**
     * @param string $url
     * @return string
     */
    public function replace($url);
}
