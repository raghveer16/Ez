<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\DataSource;

/**
 * Interface DataSourceInterface
 * @package EzAd\Ad\DataSource
 */
interface DataSourceInterface
{
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
    public function getSourceKey();

    /**
     * Moves the data source to the file specified by $localPath. The directory will be guaranteed
     * to exist and be writable.
     *
     * @param $localPath
     * @return bool
     */
    public function moveToLocalPath($localPath);

    /**
     * Must return true if this data source is a video.
     *
     * @return bool
     */
    public function isVideo();

    /**
     * Generates the preview thumbnail for this data source and saves it to $localPath.
     * This should generate the largest-size thumbnail possible, as multiple smaller ones may be
     * generated afterwards based on this one.
     *
     * @param $localPath
     * @return bool
     */
    public function generatePreview($localPath);
}
