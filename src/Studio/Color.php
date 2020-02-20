<?php

namespace EzAd\Studio;

/**
 * Outputs colors in a format that FFmpeg expects.
 *
 * 0xRRGGBB@alpha
 */
class Color
{
    /**
     * Called with 1 or 3 parameters:
     *
     * Color::rgb(r, g, b); // 0 < ... <= 255
     * Color::rgb(rgb); // 0xRRGGBB
     */
    public static function rgb()
    {
        $args = func_get_args();
        if ( count($args) == 3 ) {
            $rgb = (($args[0] & 0xff) << 16) | (($args[1] & 0xff) << 8) | ($args[2] & 0xff);
        } else if ( count($args) == 1 ) {
            $rgb = $args[0];
        }

        return sprintf('0x%06X', $rgb);
    }

    /**
     * Called with 1 or 4 parameters:
     *
     * Color::rgba(r, g, b, a); // 0 < r,g,b <= 255 ; a within [0, 255] if int, [0, 1.0] if float
     * Color::rgba(rgba); // 0xAARRGGBB
     *
     * 0 alpha = transparent, max alpha = opaque.
     */
    public static function rgba()
    {
        $args = func_get_args();
        if ( count($args) == 4 ) {
            $rgb = (($args[0] & 0xff) << 16) | (($args[1] & 0xff) << 8) | ($args[2] & 0xff);
            $alpha = $args[3];
        } else if ( count($args) == 1 ) {
            $rgb = $args[0] & 0xffffff;
            $alpha = ($args[0] >> 24) & 0xff;
        }

        if ( is_int($alpha) ) {
            $alpha /= 255;
        }
        $alpha = max(0, min(1.0, $alpha));

        return sprintf('0x%06X@%.3f', $rgb, $alpha);
    }
}
