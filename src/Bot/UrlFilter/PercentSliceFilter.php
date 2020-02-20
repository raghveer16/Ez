<?php

/**
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class PercentSliceFilter
 * @package EzAd\Bot\UrlFilter
 */
class PercentSliceFilter implements UrlFilterInterface
{
    /**
     * @var int
     */
    private $startPercent;

    /**
     * @var int
     */
    private $endPercent;

    /**
     * @param $startPercent
     * @param $endPercent
     */
    public function __construct($startPercent = 0, $endPercent = 100)
    {
        $this->startPercent = min(100, max(0, $startPercent));
        $this->endPercent = min(100, max(0, $endPercent));

        if ( $this->startPercent > $this->endPercent ) {
            $tmp = $this->startPercent;
            $this->startPercent = $this->endPercent;
            $this->endPercent = $tmp;
        }
    }

    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        if ( $this->startPercent == 0 && $this->endPercent == 100 ) {
            return $urls;
        }

        $count = count($urls);
        $start = round($count * $this->startPercent / 100);
        $end = round($count * $this->endPercent / 100);

        if ( $end >= $count ) {
            $end = $count - 1;
        }

        return array_slice($urls, $start, $end - $start);
    }
}
