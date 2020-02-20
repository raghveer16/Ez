<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Interface UrlFilterInterface
 * @package EzAd\Bot\UrlFilter
 */
interface UrlFilterInterface
{
    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls);
}
