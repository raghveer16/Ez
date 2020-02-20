<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Encapsulates an ffmpeg transcoding operation. Can be used from FFMpeg's createTranscodeOperation method,
 * or created manually:
 *
 * $operation = new TranscodeOperation('/path/to/ffmpeg', 'input.avi', 'output.m4v', []);
 * $operation->start();
 * while ( $operation->isRunning() ) {
 *   $pctComplete = $operation->getPercentCompleted();
 *   sleep(1);
 * }
 *
 * @package EzAd\Core\Media
 */
class TranscodeOperation
{
    /**
     * Prevents parsing the console output too often, i.e. if two calls are made back to back.
     */
    const REFRESH_INTERVAL_MS = 10;

    /**
     * @var float
     */
    private $previousRefresh = 0;

    /**
     * @var float
     */
    private $duration = 0;

    /**
     * @var float
     */
    private $position = 0;

    /**
     * @var Process
     */
    private $process;

    /**
     * Initializes properties of the transcoding operation.
     *
     * The options include the following:
     * - env: array of environment variables to set on the ffmpeg process
     * - startpos: position of the source video to start transcoding from, in seconds
     * - endpos: position of the source video to stop transcoding, in seconds
     * - volume_adjust: decibel adjustment for volume, generally pulled from AudioNormalizer
     * - mute: if true, completely remove audio from the output file
     * - threads: the number of threads ffmpeg should use, default 2
     *
     * @param string $ffmpegPath Path to the ffmpeg binary
     * @param string $input Path to the input file
     * @param string $output Path to the output file
     * @param array $options A list of options.
     */
    public function __construct($ffmpegPath, $input, $output, array $options)
    {
        // /usr/local/bin/ffmpeg -v info -i 'test_input.mp4' -threads 3 -r 30000/1001 -b 2M -bt 4M
        //   -vcodec libx264 -preset medium -crf 23 -acodec libfaac -ac 2 -ar 48000 -ab 192k 'test_output.mp4'

        $builder = new ProcessBuilder([$ffmpegPath, '-v', 'info', '-i', $input]);

        if ( isset($options['env']) && is_array($options['env']) ) {
            $builder->addEnvironmentVariables($options['env']);
        }

        if ( isset($options['startpos']) && is_numeric($options['startpos']) ) {
            $builder->add('-ss', (int) $options['startpos']);
        }
        if ( isset($options['endpos']) && is_numeric($options['endpos']) ) {
            $builder->add('-to', (int) $options['endpos']);
        }
        if ( isset($options['volume_adjust']) && is_numeric($options['volume_adjust'])
            && $options['volume_adjust'] != 0 ) {
            $builder->add('-af', 'volume=' . $options['volume_adjust'] . 'dB');
        }

        $threads = isset($options['threads']) && is_numeric($options['threads']) ? intval($options['threads']) : 2;

        $builder
            ->add('-threads')->add($threads)
            ->add('-r')->add('30000/1001')
            ->add('-b')->add('2M')
            ->add('-bt')->add('4M')
            ->add('-vcodec')->add('libx264')
            ->add('-preset')->add('medium')
            ->add('-crf')->add('23');

        if ( isset($options['mute']) && $options['mute'] ) {
            $builder->add('-an');
        } else {
            $builder
                ->add('-acodec')->add('libfaac')
                ->add('-ac')->add('2')
                ->add('-ar')->add('48000')
                ->add('-ab')->add('192k');
        }

        $builder->add($output);
        $this->process = $builder->getProcess();
    }

    /**
     * Starts the transcode operation.
     */
    public function start()
    {
        $this->process->start();
    }

    /**
     * Checks if the operation is still running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->process->isRunning();
    }

    /**
     * Gets the duration of the file in seconds.
     *
     * @return float
     */
    public function getDuration()
    {
        $this->refreshInformation();
        return $this->duration;
    }

    /**
     * Gets the current position of the operation, in seconds.
     *
     * @return float
     */
    public function getPosition()
    {
        $this->refreshInformation();
        return $this->position;
    }

    /**
     * Gets the percent completed as a value between 0 and 1.
     *
     * @return float
     */
    public function getPercentCompleted()
    {
        $this->refreshInformation();
        return $this->duration == 0 ? 0 : ($this->position / $this->duration);
    }

    /**
     * Refreshes internal duration and position values based on process output.
     */
    private function refreshInformation()
    {
        $now = microtime(true) * 1000;
        if ( $now < $this->previousRefresh + self::REFRESH_INTERVAL_MS ) {
            return;
        }
        $this->previousRefresh = $now;

        $data = $this->process->getIncrementalErrorOutput();
        if ( preg_match('/Duration: ([0-9:.]+)/', $data, $m) ) {
            $this->duration = $this->timespecToSeconds($m[1]);
        }

        if ( preg_match_all('/time=([0-9:.]+)/', $data, $m) ) {
            $match = $m[1];
            $this->position = $this->timespecToSeconds(end($match));
        }
    }

    /**
     * Converts a timespec (HH:MM:SS.ss) to a floating point seconds value.
     *
     * @param $spec
     * @return int
     */
    private function timespecToSeconds($spec)
    {
        $p = explode(':', $spec);
        if ( count($p) != 3 ) {
            return 0;
        }
        return intval($p[0]) * 3600 + intval($p[1]) * 60 + floatval($p[2]);
    }
}
