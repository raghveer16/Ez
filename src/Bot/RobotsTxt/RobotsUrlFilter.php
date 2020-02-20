<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\RobotsTxt;

use EzAd\Bot\UrlFilter\UrlFilterInterface;

/**
 * Class RobotsUrlFilter
 * @package EzAd\Bot\RobotsTxt
 */
class RobotsUrlFilter implements UrlFilterInterface
{
    /**
     * @var RobotsTxtFile
     */
    private $robotsTxt;

    /**
     * @param RobotsTxtFile $robotsTxtFile
     */
    public function __construct(RobotsTxtFile $robotsTxtFile)
    {
        $this->robotsTxt = $robotsTxtFile;
    }

    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        return array_filter($urls, function($url) {
            return $this->robotsTxt->isUrlAllowed($url);
        });
    }
}