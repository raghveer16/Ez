<?php

namespace EzAd\Media\Subtitle;

/**
 * Writes subtitles out in the Advanced Substation Alpha format where it can be read by the video encoder.
 *
 * Simplest command to add subtitles: ffmpeg -i input.m4v -vf "ass=file.ass" output.m4v
 */
class AssWriter
{
    private $data;

    public function __construct($title = 'EZ-AD Studio Subtitles', $resX = 1280, $resY = 720)
    {
        $this->data = trim(str_replace(
            ['@script.title', '@resolution.x', '@resolution.y'],
            [$title, $resX, $resY],
            file_get_contents(__DIR__ . '/ass_header.txt')
        ));
    }

    public function writeEntry(Entry $entry)
    {
        // Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text

        $layer = 0;
        $start = self::secondsToTimespec($entry->startSeconds);
        $end   = self::secondsToTimespec($entry->endSeconds);
        $style = 'Default';
        $name  = '';
        $ml = $mr = $mv = 0;
        $effect = '';

        $tags = '{' . $entry->getAssOverrideTags() . '}';
        $text = $tags . str_replace(['{', '}'], ['\{', '\}'], $entry->text);
        $text = preg_replace('/\r?\n/', '\n', $text); // strip actual newlines down to raw '\n'
        $this->data .= "\nDialogue: $layer,$start,$end,$style,$name,$ml,$mr,$mv,$effect,$text";
    }

    public function getData()
    {
        return $this->data;
    }

    public static function secondsToTimespec($sec)
    {
        // convert e.g. 93.4 to "0:01:33.40"

        $intsec = (int) $sec;
        $centisec = floor(100 * ($sec - $intsec));

        $hours = floor($intsec / 3600);
        $minutes = floor(($intsec % 3600) / 60);
        $seconds = $intsec % 60;

        return sprintf('%d:%02d:%02d.%02d', $hours, $minutes, $seconds, $centisec);
    }
}
