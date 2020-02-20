<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;
use EzAd\Util\Pattern\WriteOnceTrait;

/**
 * Contains information on a media file. Objects of this type are generally created by the
 * FFProbe class and not manually.
 *
 * @package EzAd\Core\Media
 */
class MediaInfo
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $streams = [];

    /**
     * @var array
     */
    private $streamObjects = [];

    /**
     * Creates the MediaInfo based on data gathered from ffprobe.
     *
     * @param array $probeData
     */
    public function __construct(array $probeData)
    {
        $this->data = $probeData['format'];
        $this->streams = isset($probeData['streams']) ? $probeData['streams'] : [];
    }

    /**
     * Gets the name of the file scanned.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->data['filename'];
    }

    /**
     * Gets the number of streams in the file.
     *
     * @return int
     */
    public function getStreamCount()
    {
        return $this->data['nb_streams'];
    }

    /**
     * Gets the stream at the given index, either audio, video, or other (subtitle?).
     *
     * @param $index
     * @throws \OutOfBoundsException
     * @return Stream|AudioStream|VideoStream
     */
    public function getStream($index)
    {
        if ( !isset($this->streams[$index]) ) {
            throw new \OutOfBoundsException('Invalid stream index');
        }

        if ( !isset($this->streamObjects[$index]) ) {
            $stream = $this->streams[$index];
            if ( $stream['codec_type'] === 'audio' ) {
                $obj = new AudioStream($stream);
            } else if ( $stream['codec_type'] === 'video' ) {
                $obj = new VideoStream($stream);
            } else {
                $obj = new Stream($stream);
            }
            $this->streamObjects[$index] = $obj;
        }

        return $this->streamObjects[$index];
    }

    /**
     * Gets an array of streams of the given type - either audio or video.
     *
     * @param $type
     * @return Stream[]
     */
    public function getStreamsByType($type)
    {
        $streamList = [];
        foreach ( $this->streams as $index => $stream ) {
            if ( $stream['codec_type'] === $type ) {
                $streamList[] = $this->getStream($index);
            }
        }

        return $streamList;
    }

    /**
     * Gets all of the audio streams in the file.
     *
     * @return AudioStream[]
     */
    public function getAudioStreams()
    {
        return $this->getStreamsByType('audio');
    }

    /**
     * Gets all of the video streams in the file.
     *
     * @return VideoStream[]
     */
    public function getVideoStreams()
    {
        return $this->getStreamsByType('video');
    }

    /**
     * Gets a list of format names.
     *
     * @return array
     */
    public function getFormatNames()
    {
        return explode(',', $this->data['format_name']);
    }

    /**
     * Gets the long name of the container format.
     *
     * @return string
     */
    public function getFormatLongName()
    {
        return $this->data['format_long_name'];
    }

    /**
     * Gets the duration of the file, in seconds.
     *
     * @return float
     */
    public function getDuration()
    {
        return (float) $this->data['duration'];
    }

    /**
     * Gets the size of the file.
     *
     * @return int
     */
    public function getSize()
    {
        return (int) $this->data['size'];
    }

    /**
     * Gets the overall bitrate of the file.
     *
     * @return int
     */
    public function getBitRate()
    {
        return (int) $this->data['bit_rate'];
    }

    /**
     * Gets all tags.
     *
     * @return array
     */
    public function getAllTags()
    {
        return $this->data['tags'];
    }

    /**
     * Gets any value from the data array.
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * Gets the entire data array.
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }
}
