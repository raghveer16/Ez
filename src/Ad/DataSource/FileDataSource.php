<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\DataSource;

/**
 * Ad data source that pulls from a local file.
 *
 * @package EzAd\Ad\DataSource
 */
class FileDataSource implements DataSourceInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path The path to the local file.
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the source key for checking data duplication, or null to not do so. Makes sense
     * for things like the YouTube data source where you wouldn't want to download the same
     * video twice if we already have it. An example for YouTube would be the video ID.
     *
     * This value must be 40 characters or less.
     *
     * For example - yt:video_id, file:hash_of_file_data
     *
     * @return string
     */
    public function getSourceKey()
    {
        // 5 + 28 chars.
        return 'file:' . base64_encode(hash_file('sha1', $this->path, true));
    }

    /**
     * Moves the data source to the file specified by $localPath. The directory will be guaranteed
     * to exist and be writable.
     *
     * @param $localPath
     * @return bool
     */
    public function moveToLocalPath($localPath)
    {
        return rename($this->path, $localPath);
    }

    /**
     * Must return true if this data source is a video.
     *
     * @return bool
     */
    public function isVideo()
    {
        $im = @getimagesize($this->path);
        return !$im || !in_array($im[2], [IMAGETYPE_GIF, IMAGETYPE_BMP, IMAGETYPE_JPEG, IMAGETYPE_PNG]);
    }

    /**
     * Generates the preview thumbnail for this data source and saves it to $localPath.
     * This should generate the largest-size thumbnail possible, as multiple smaller ones may be
     * generated afterwards based on this one.
     *
     * @param $localPath
     * @return bool
     */
    public function generatePreview($localPath)
    {

    }
}
