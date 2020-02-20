<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;

/**
 * Base class containing common properties for audio and video streams.
 *
 * @package EzAd\Core\Media
 */
class Stream
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Creates the base stream given the data for the stream from ffprobe.
     *
     * @param array $probeStream
     */
    public function __construct(array $probeStream)
    {
        $this->data = $probeStream;
        // index, codec_name, codec_long_name, codec_type, codec_tag_string, duration, bit_rate, nb_frames,
        // disposition (obj), tags (obj)
    }

    public function getIndex()
    {
        return (int) $this->data['index'];
    }

    public function getCodecName()
    {
        return $this->data['codec_name'];
    }

    public function getCodecLongName()
    {
        return $this->data['codec_long_name'];
    }

    public function getCodecType()
    {
        return $this->data['codec_type'];
    }
    
    public function getCodecTag()
    {
        return $this->data['codec_tag_string'];
    }

    public function getDuration()
    {
        return (float) $this->data['duration'];
    }

    public function getBitRate()
    {
        return (int) $this->data['bit_rate'];
    }

    public function getFrameCount()
    {
        return (int) $this->data['nb_frames'];
    }

    /**
     * @return array
     */
    public function getDisposition()
    {
        return $this->data['disposition'];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->data['tags'];
    }
} 