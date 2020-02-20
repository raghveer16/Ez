<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class MiddleOutFilter
 * @package EzAd\Bot\UrlFilter
 */
class MiddleOutFilter implements UrlFilterInterface
{
    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        // start from center, go down 1, up 2, down 3, etc.
        $count = count($urls);
        if ( $count < 3 ) {
            return $urls;
        }

        $center = (int) floor(($count - 1) / 2);
        $index = $center;
        $newUrls = [];
        $inc = 1;

        while ( $index >= 0 && $index < $count ) {
            $newUrls[] = $urls[$index];
            $index += $inc;
            if ( $inc > 0 ) {
                $inc = -($inc + 1);
            } else {
                $inc = -($inc - 1);
            }
        }

        return $newUrls;
    }
}
