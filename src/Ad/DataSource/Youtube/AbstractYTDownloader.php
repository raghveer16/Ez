<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\DataSource\Youtube;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Abstract base for a Youtube downloader. Two implementations will probably be the simple one
 * we already have, then one based on youtube-dl in the future which is more powerful.
 *
 * @package EzAd\Ad\DataSource\Youtube
 */
abstract class AbstractYTDownloader
{
    /**
     * @var array
     */
    private $videoInfoCache = [];

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'defaults' => [
                'cookies' => true,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 '
                        . '(KHTML, like Gecko) Chrome/35.0.1916.114 Safari/537.36',
                ]
            ]
        ]);
    }

    /**
     * Attempts to extract the video ID from the given YouTube URL. Returns an empty string if it fails.
     *
     * @param $url
     * @return string
     */
    public static function convertUrlToID($url)
    {
        if ( preg_match('#youtu\.be/([a-zA-Z0-9_-]+)#', $url, $match) ) {
            return $match[1];
        }

        $info = parse_url($url);
        if ( !isset($info['query']) ) {
            return '';
        }

        parse_str($info['query'], $query);
        return isset($query['v']) ? $query['v'] : '';
    }

    /**
     * Downloads the video at the given $url into the file at $localPath.
     *
     * @param $videoId
     * @param $localPath
     * @return bool
     */
    abstract public function download($videoId, $localPath);

    public function downloadThumbnail($videoId, $localPath)
    {
        $videoInfo = $this->getVideoInfo($videoId);
        $this->streamingGet($videoInfo->thumbnail, $localPath);
    }

    /**
     * Downloads the given $url to the file located at $localPath. Streams to reduce memory footprint.
     *
     * @param $url
     * @param $localPath
     */
    public function streamingGet($url, $localPath)
    {
        $file = fopen($localPath, 'w');
        $this->client->get($url, ['save_to' => $file]);
        fclose($file);
    }

    /**
     * @param $videoId
     * @return YTUrlCollection
     */
    public function getVideoUrls($videoId)
    {
        /** @var ResponseInterface $response */
        $response = $this->client->get('http://www.youtube.com/watch?v=' . $videoId);
        $contents = $response->getBody();

        $search = '"url_encoded_fmt_stream_map": "';
        $pos = strpos($contents, $search);

        if ( $pos === false ) {
            return false;
        }

        $pos += strlen($search);
        $nextQuote = strpos($contents, '"', $pos);

        $raw = substr($contents, $pos, $nextQuote - $pos);
        $split = explode(',', $raw);

        $mapping = array();

        foreach ( $split as $part ) {
            $urlparts = explode('\u0026', $part);
            $ary = array();
            foreach ( $urlparts as $urlp ) {
                list($key, $value) = explode('=', $urlp);
                $ary[$key] = urldecode($value);
            }

            $info = new YTUrlInfo();
            $info->quality = $ary['quality'];
            $info->type = $ary['type'];
            $info->url = $ary['url'];
            $info->sig = isset($ary['sig']) ? $ary['sig'] : '';

            $mapping[] = $info;
        }

        return new YTUrlCollection($mapping);
    }

    /**
     * @param $videoId
     * @return YTVideoInfo
     */
    public function getVideoInfo($videoId)
    {
        if ( isset($this->videoInfoCache[$videoId]) ) {
            return $this->videoInfoCache[$videoId];
        }

        /** @var ResponseInterface $response */
        $response = $this->client->get("http://gdata.youtube.com/feeds/api/videos/$videoId?v=2&alt=jsonc");
        $json = $response->json();

        return $this->videoInfoCache[$videoId] = YTVideoInfo::fromJson($json);
    }
}

/**
 * Class YTVideoInfo
 * @package EzAd\Ad\DataSource\Youtube
 */
class YTVideoInfo
{
    public $title;
    public $thumbnail;
    public $duration;

    static public function fromJson($json)
    {
        $d = $json->data;

        $vi = new self();
        $vi->title = $d->title;
        $vi->thumbnail = isset($d->thumbnail->hqDefault) ? $d->thumbnail->hqDefault : $d->thumbnail->sqDefault;
        $vi->duration = $d->duration;

        return $vi;
    }
}

/**
 * Class YTUrlInfo
 * @package EzAd\Ad\DataSource\Youtube
 */
class YTUrlInfo
{
    public $quality;
    public $type;
    public $url;
    public $sig;

    public function getUrl()
    {
        return $this->url . '&signature=' . $this->sig;
    }
}

/**
 * Class YTUrlCollection
 * @package EzAd\Ad\DataSource\Youtube
 */
class YTUrlCollection
{
    public $urls;
    public static $qualities = array(
        'hd1080' => 100,
        'hd720'  => 80,
        'large'  => 60,
        'medium' => 40,
        'small'  => 20,
    );

    public function __construct($urls)
    {
        $this->urls = $urls;
    }

    // tries for hd1080 quality, type = video/mp4
    // falls back to hd720, large, medium, small
    public function sortedByBest()
    {
        $mp4 = $this->onlyMp4();
        usort($mp4, function($a, $b) {
            return self::$qualities[$b->quality] - self::$qualities[$a->quality];
        });
        return $mp4;
    }

    private function onlyMp4()
    {
        return array_filter($this->urls, function($info) {
            return strpos($info->type, 'video/mp4') !== false;
        });
    }
}

