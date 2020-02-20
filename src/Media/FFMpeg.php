<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;

/**
 * FFmpeg serves as a utility interface between code and the ffmpeg command line application.
 *
 * @package EzAd\Core\Media
 */
class FFMpeg
{
    /**
     * @var string
     */
    private $ffmpegPath;

    /**
     * @var FFProbe
     */
    private $ffProbe;

    /**
     * @param string $ffmpegPath
     * @param FFProbe $ffProbe
     */
    public function __construct($ffmpegPath, FFProbe $ffProbe)
    {
        $this->ffmpegPath = $ffmpegPath;
        $this->ffProbe = $ffProbe;
    }

    public function createTranscodeOperation($input, $output, array $options)
    {
        return new TranscodeOperation($this->ffmpegPath, $input, $output, $options);
    }
}
