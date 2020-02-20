<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\Storage;

/**
 * Storage driver for advertisements that persists data into Google's Cloud Storage platform.
 *
 * @package EzAd\Ad\Storage
 */
class GoogleCloudStorage implements StorageInterface
{
    /**
     * @var \EzAd\Util\GCSUploader
     */
    private $uploader;

    private $bucketName;

    private $failsafeStorage;

    private $retries = 5;

    /**
     *
     */
    public function __construct(\EzAd\Util\GCSUploader $uploader, $bucketName)
    {
        $this->uploader = $uploader;
        $this->bucketName = $bucketName;
    }

    public function setFailsafeStorage(StorageInterface $storage)
    {
        $this->failsafeStorage = $storage;
    }

    public function setRetries($retries)
    {
        $this->retries = $retries;
    }

    public function store($path, array $options = [])
    {
        $date = date('Y/m/d');

        if ( isset($options['timestamp']) ) {
            if ( $options['timestamp'] instanceof \DateTime ) {
                $date = $options['timestamp']->format('Y/m/d');
            } else if ( is_numeric($options['timestamp']) && $options['timestamp'] > 0 ) {
                $date = date('Y/m/d', $options['timestamp']);
            }
        }

        // closure that receives numChunks, totalChunks, attemptNum
        $listener = isset($options['listener']) ? $options['listener'] : null;

        $newName = $date . '/' . basename($path);
        try {
            $ok = $this->uploader->retryingUpload($path, $this->bucketName, $newName, $listener, [], $this->retries);
        } catch ( \Google_Service_Exception $e ) {
            $options['fallback_error'] = $e->getMessage();
            $ok = false;
        }

        if ( !$ok && $this->failsafeStorage ) {
            return $this->failsafeStorage->store($path, $options);
        }

        return 'https://storage.googleapis.com/' . $this->bucketName . '/' . $newName;
    }
}
