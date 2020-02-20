<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;
use Symfony\Component\Process\Process;

/**
 * Provides an interface to the `ffprobe` command for extracting information from media files.
 *
 * @package EzAd\Core\Media
 */
class FFProbe
{
    /**
     * @var string
     */
    private $ffprobePath;

    /**
     * @var array|null
     */
    private $commandEnv = null;

    /**
     * @param string $ffprobePath Path to the ffprobe binary
     * @param null|array $env Environment variables for the command
     */
    public function __construct($ffprobePath, $env = null)
    {
        $this->ffprobePath = $ffprobePath;
        $this->commandEnv = $env;
    }

    /**
     * Loads media information for the given file, or null in case of error.
     *
     * @param $file
     * @return MediaInfo|null
     */
    public function load($file)
    {
        $file = escapeshellarg($file);
        $proc = new Process($this->ffprobePath . " -show_streams -show_format -print_format json $file",
            null, $this->commandEnv);

        $proc->run();
        $output = $proc->getOutput();
        $json = json_decode($output, true);
        if ( $json ) {
            return new MediaInfo($json);
        } else {
            return null;
        }
    }
}
