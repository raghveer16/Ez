<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Utilities to simplify uploading files to Google Cloud Storage.
 *
 * @package EzAd\Util
 */
class GCSUploader
{
    /**
     * @var \Google_Client
     */
    private $client;

    /**
     * @var \Google_Service_Storage
     */
    private $storage;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * Default chunk size for uploads, 16MB.
     */
    const DEFAULT_CHUNK_SIZE = 16777216;

    /**
     * @param \Google_Client $client
     * @param int $chunkSize
     */
    public function __construct(\Google_Client $client, $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->client = $client;
        $this->storage = new \Google_Service_Storage($client);
        $this->chunkSize = $chunkSize;
    }

    /**
     * Uploads a local file to the given bucket, optionally renaming it. By default, it creates a file
     * in the root of the bucket named after the file's basename.
     *
     * @param string $localPath
     * @param string $bucketName
     * @param null|string $remoteName
     * @param callable $listener Receives ($numChunksUploaded, $totalChunks) as parameters.
     * @param array $meta
     * @return bool
     */
    public function upload($localPath, $bucketName, $remoteName = null, \Closure $listener = null, array $meta = [])
    {
        if ( $remoteName === null ) {
            $remotePath = basename($localPath);
        } else if ( $remoteName[ strlen($remoteName) - 1] === '/' ) {
            $remotePath = $remoteName . basename($localPath);
        } else {
            $remotePath = $remoteName;
        }

        $object = new \Google_Service_Storage_StorageObject();
        $object->setName($remotePath);
        $object->setMd5Hash(base64_encode(hash_file('md5', $localPath, true)));
        $object->setMetadata(array_merge([
            'source' => gethostname() . ':' . $localPath,
        ], $meta));

        $this->client->setDefer(true);
        $request = $this->storage->objects->insert($bucketName, $object, [
            'predefinedAcl' => 'publicRead',
        ]);

        $mimeType = MimeTypeGuesser::getInstance()->guess($localPath);
        $media = new \Google_Http_MediaFileUpload($this->client, $request, $mimeType, null, true, $this->chunkSize);
        $fileSize = filesize($localPath);
        $media->setFileSize($fileSize);

        // Upload the various chunks. $status will be false until the process is complete.
        $status = false;
        $handle = fopen($localPath, 'rb');
        $nchunks = ceil($fileSize / $this->chunkSize);
        $chunkPos = 0;

        while ( !$status && !feof($handle) ) {
            $chunk = fread($handle, $this->chunkSize);
            $status = $media->nextChunk($chunk);
            $chunkPos++;
            if ( $listener ) {
                $listener($chunkPos, $nchunks);
            }
        }

        // The final value of $status will be the data from the API for the object that has been uploaded.
        $result = false;
        if ( $status != false ) {
            $result = $status;
        }

        fclose($handle);
        $this->client->setDefer(false);

        return $result;
    }

    /**
     * @param $localPath
     * @param $bucketName
     * @param null|string $remoteName
     * @param callable $listener
     * @param array $meta
     * @return bool
     */
    public function retryingUpload($localPath, $bucketName, $remoteName = null, \Closure $listener = null,
                                   array $meta = [], $numRetries = 5)
    {
        $numRetries = 5;
        $attemptNo = 1;

        // extend listener to include the attempt #
        if ( $listener ) {
            $tmp = $listener;
            $listener = function($nc, $total) use ($tmp, &$attemptNo) {
                $tmp($nc, $total, $attemptNo);
            };
        }

        $result = false;
        while ( true ) {
            try {
                $result = $this->upload($localPath, $bucketName, $remoteName, $listener, $meta);
            } catch ( \Exception $e ) {
                $result = false;
            }

            if ( $result ) {
                break;
            }

            if ( $attemptNo <= $numRetries ) {
                $delay = 1000000 * pow(2, $attemptNo);
                usleep($delay + mt_rand(0, 1000000)); // exp. backoff plus 0-1 seconds.
                $attemptNo++;
            } else {
                break;
            }
        }

        return $result;
    }
}
