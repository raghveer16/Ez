<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * <UrlFilters>
 *   <RegexFilter regex="/blah/i" />
 * </UrlFilters>
 *
 * "UrlFilters": [
 *   {
 *     "id": "RegexFilter",
 *     "regex": "/blah/i"
 *   }
 * ]
 *
 * Class RegexFilter
 * @package EzAd\Bot\UrlFilter
 */
class RegexFilter implements UrlFilterInterface
{
    private $regex;

    /**
     * @param $regex
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        $newUrls = [];
        foreach ( $urls as $url ) {
            if ( preg_match($this->regex, $url) ) {
                $newUrls[] = $url;
            }
        }
        return $newUrls;
    }
}
