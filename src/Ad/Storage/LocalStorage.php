<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\Storage;

/**
 * Stores advertisements in a local path on the server.
 *
 * $app['ad_storage'] = function($app) {
 *   return new LocalStorage('/home/heyads/www/private/ads', 'https://ezadtv.com/private/ads');
 * };
 *
 *
 * @package EzAd\Ad\Storage
 */
class LocalStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Configures the local storage with the absolute path on the server and the base URL to that path.
     *
     * @param string $filePath
     * @param string $baseUrl
     */
    public function __construct($filePath, $baseUrl)
    {
        $this->filePath = $filePath;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $path
     * @param array $options
     * @return string
     */
    public function store($path, array $options = [])
    {
        $timestamp = (isset($options['timestamp']) && $options['timestamp'] > 0) ? $options['timestamp'] : time();

        $sub = date('Y/m/d', $timestamp);
        $folder = $this->filePath . '/' . $sub;

        if ( !is_dir($folder) ) {
            mkdir($folder, 0777, true);
        }
        
        // don't overwrite existing files unless overwrite option is true
        if ( is_file($folder . '/' . basename($path)) && (!isset($options['overwrite']) || !$options['overwrite']) ) {
            return $this->baseUrl . '/' . $sub . '/' . basename($path);
        }

        if ( isset($options['link']) && $options['link'] ) {
            link($path, $folder . '/' . basename($path));
        } else {
            copy($path, $folder . '/' . basename($path));
        }
        return $this->baseUrl . '/' . $sub . '/' . basename($path);
    }
}
