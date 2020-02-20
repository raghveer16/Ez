<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\DataSource\Youtube;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Uses the simple YouTube downloader code written for EZ-AD a long time ago. Updated to use
 * Guzzle4 instead of the ancient Zend Framework 1 HTTP library.
 *
 * @package EzAd\Ad\DataSource\Youtube
 */
class SimpleYTDownloader extends AbstractYTDownloader
{
    /**
     * Downloads the video at the given $url into the file at $localPath.
     *
     * @param $url
     * @param $localPath
     * @return bool
     */
    public function download($url, $localPath)
    {
        $videoId = self::convertUrlToID($url);
        $urlList = $this->getVideoUrls($videoId);
        $best = $urlList->sortedByBest();

        if ( count($best) > 0 ) {
            $this->streamingGet($best[0], $localPath);
        }
    }
}
