<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\ProductStore;
use EzAd\Util\GCSUploader;
use GuzzleHttp\Stream\Stream;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Manages storage of product images via Google Cloud Storage.
 *
 * @package EzAd\Bot\ProductStore
 */
class ImageStorage
{
    /**
     * @var GCSUploader
     */
    private $uploader;

    /**
     * @var string
     */
    private $bucketName;

    /**
     * @param GCSUploader $uploader
     * @param string $bucketName
     */
    public function __construct(GCSUploader $uploader, $bucketName = 'prodimg.ezadtv.com')
    {
        $this->uploader = $uploader;
        $this->bucketName = $bucketName;
    }

    /**
     * @param $url
     * @param string $domain
     * @return string
     */
    public function copyRemoteImage($url, $domain = '')
    {
        // download the remote URL (should be abstracted into ezad-lib)
        $localTmp = tempnam(sys_get_temp_dir(), 'ezbot');
        $localFile = fopen($localTmp, 'w');
        $localStream = Stream::factory($localFile);

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);

        $stream = $response->getBody();
        \GuzzleHttp\Stream\Utils::copyToStream($stream, $localStream);
        fclose($localFile);

        // create the new filename
        $hash = hash('sha1', $url, true);
        $file = new File($localTmp);
        $newName = \Google_Utils::urlSafeB64Encode($hash) . '.' . $file->guessExtension();
        if ( !$domain ) {
            $domain = parse_url($url, PHP_URL_HOST);
        }

        // upload the local temp file remotely
        $ok = $this->uploader->retryingUpload($localTmp, $this->bucketName, $domain . '/' . $newName);

        unlink($localTmp);

        if ( $ok ) {
            return 'http://' . $this->bucketName . "/$domain/$newName";
        }
        return false;
    }

    /**
     * @param $path
     * @param $domain
     * @param string $newName
     * @return string
     */
    public function copyLocalImage($path, $domain, $newName = '')
    {
        $remotePath = $domain . '/' . $newName;
        $ok = $this->uploader->retryingUpload($path, $this->bucketName, $remotePath);

        if ( $ok ) {
            return 'http://' . $this->bucketName . '/' . $remotePath;
        }
        return false;
    }

    /**
     * Copy images from the given product to GCS.
     *
     * @param Product $product
     */
    public function copyProductImages(Product $product)
    {
        $remoteImages = $product->getImages();
        $gcsImages = [];

        foreach ( $remoteImages as $remoteUrl ) {
            $newImage = $this->copyRemoteImage($remoteUrl, $product->getDomain());
            if ( $newImage ) {
                $gcsImages[] = $newImage;
            }
        }

        $product->setImages($gcsImages);
    }
}
