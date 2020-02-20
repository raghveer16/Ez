<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class TopBottomFilter
 * @package EzAd\Bot\UrlFilter
 */
class TopBottomFilter implements UrlFilterInterface
{
    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        // basically a no-op
        return $urls;
    }
}
