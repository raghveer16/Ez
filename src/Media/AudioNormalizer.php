<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;
use Symfony\Component\Process\Process;

/**
 * Class AudioNormalizer
 * @package EzAd\Core\Media
 */
class AudioNormalizer
{
    /**
     * @var string
     */
    private $ffprobePath;

    /**
     * @var array
     */
    private $commandEnv = [];

    /**
     * @param string $ffprobePath
     * @param null|array $env
     */
    public function __construct($ffprobePath, $env = null)
    {
        $this->ffprobePath = $ffprobePath;
        $this->commandEnv = $env;
    }

    /**
     * Calculates the required volume adjustment for a video file to reach the given target decibel value.
     * Pass the return value to the 'volume_adjust' option of the transcoder.
     *
     * @param $file
     * @param $targetDecibels
     * @return float
     */
    public function getVolumeAdjustment($file, $targetDecibels = -20)
    {
        $file = escapeshellarg($file);
        $cmd = $this->ffprobePath . " -v error -of compact=p=0:nk=1 -show_entries frame_tags=lavfi.r128.I "
            . "-f lavfi amovie=$file,ebur128=metadata=1";

        $process = new Process($cmd, null, $this->commandEnv);
        $process->run();

        $out = explode("\n", $process->getOutput());

        $avg = $targetDecibels;
        foreach ( $out as $frameDecibels ) {
            $db = (float) $frameDecibels;
            if ( $db ) {
                $avg = $db;
            }
        }

        $diff = $targetDecibels - $avg;
        return abs($diff) < 0.0001 ? 0 : $diff;
    }
}
