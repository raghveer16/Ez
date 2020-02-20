<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class BottomUpFilter
 * @package EzAd\Bot\UrlFilter
 */
class BottomUpFilter implements UrlFilterInterface
{
    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        return array_reverse($urls);
    }
}
