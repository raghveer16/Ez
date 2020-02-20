<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\DataSource;
use EzAd\Ad\DataSource\Youtube\AbstractYTDownloader;

/**
 * Data source for YouTube videos.
 *
 * @package EzAd\Ad\DataSource
 */
class YoutubeDataSource implements DataSourceInterface
{
    /**
     * @var AbstractYTDownloader
     */
    private $downloader;

    /**
     * @var string
     */
    private $videoId;

    /**
     * @var bool
     */
    private $mute;

    /**
     * @var int
     */
    private $startSeconds;

    /**
     * @var int
     */
    private $endSeconds;

    /**
     * @param AbstractYTDownloader $downloader
     * @param string $videoId
     * @param bool $mute
     * @param int $startSeconds
     * @param int $endSeconds
     */
    public function __construct(AbstractYTDownloader $downloader, $videoId, $mute = false, $startSeconds = null,
                                $endSeconds = null)
    {
        $this->downloader = $downloader;
        $this->videoId = $videoId;
        $this->mute = $mute;
        $this->startSeconds = $startSeconds;
        $this->endSeconds = $endSeconds;
    }

    /**
     * @return int
     */
    public function getEndSeconds()
    {
        return $this->endSeconds;
    }

    /**
     * @return boolean
     */
    public function isMute()
    {
        return $this->mute;
    }

    /**
     * @return int
     */
    public function getStartSeconds()
    {
        return $this->startSeconds;
    }

    /**
     * @return string
     */
    public function getVideoId()
    {
        return $this->videoId;
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
        // yt:video_id:start_seconds:end_seconds:mute

        // 14 + 4 + 4 + 2 = 24 chars within current limits, 3-digit start/end and 11 char video ids.
        // 16 + 5 + 5 + 2 = 28 chars high estimate, with 4-digit start/end times and 13 char video ids.
        return 'yt:' . $this->videoId
            . ':' . ($this->startSeconds === null ? 'N' : $this->startSeconds)
            . ':' . ($this->endSeconds === null ? 'N' : $this->endSeconds)
            . ':' . ($this->mute ? 'Y' : 'N');
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
        try {
            $this->downloader->download($this->getVideoId(), $localPath);
        } catch ( \Exception $e ) {
            return false;
        }

        return true;
    }

    /**
     * Must return true if this data source is a video.
     *
     * @return bool
     */
    public function isVideo()
    {
        return true;
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
        try {
            $this->downloader->downloadThumbnail($this->getVideoId(), $localPath);
        } catch ( \Exception $e ) {
            return false;
        }

        return true;
    }
}
