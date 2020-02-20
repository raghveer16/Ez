<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;
use EzAd\Util\Geometry\Size;

/**
 * Extends the base Stream class for any video-specific stream fields.
 *
 * @package EzAd\Core\Media
 */
class VideoStream extends Stream
{
    public function __construct($probeStream)
    {
        parent::__construct($probeStream);
        // width, height, sample_aspect_ratio, display_aspect_ratio, pix_fmt
    }

    /**
     * Gets the dimensions of the video.
     *
     * @return Size
     */
    public function getDimensions()
    {
        return new Size($this->data['width'], $this->data['height']);
    }

    /**
     * Gets the pixel sampling ratio (stretching, etc.). Generally 1:1.
     *
     * @return Size
     */
    public function getSampleRatio()
    {
        return $this->ratioToSize($this->data['sample_aspect_ratio']);
    }

    /**
     * Gets the display ratio. 4:3 for SD, 16:9 for HD, etc.
     *
     * @return Size
     */
    public function getDisplayRatio()
    {
        return $this->ratioToSize($this->data['display_aspect_ratio']);
    }

    /**
     * Gets the pixel format of the video.
     *
     * @return string
     */
    public function getPixelFormat()
    {
        return $this->data['pix_fmt'];
    }

    /**
     * Converts an aspect ratio (width:height) to a Size instance.
     *
     * @param $ratio
     * @return Size
     */
    private function ratioToSize($ratio)
    {
        $wh = explode(':', $ratio);
        return new Size((int) $wh[0], (int) $wh[1]);
    }
}
