<?php

/**
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\Storage;

/**
 * Interface for ad storage drivers.
 *
 * @package EzAd\Ad\Storage
 */
interface StorageInterface
{
    /**
     * Stores the given advertisement and returns the publicly accessible URLs for the main file
     * and dependent files.
     *
     * @param string $path
     * @param array $options Optional parameters that may be used by implementations.
     * @return string
     */
    public function store($path, array $options = []);
}
