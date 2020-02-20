<?php

/**
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad;

/**
 * Class AdSource
 * @package EzAd\Ad
 */
class AdSource
{
    public static function file($path)
    {
        return 'file:' . base64_encode(hash_file('sha1', $path, true));
    }

    public static function youtube($videoId, $startSec = null, $endSec = null, $mute = false)
    {
        if ( $startSec === null ) {
            $startSec = 'N';
        }
        if ( $endSec === null ) {
            $endSec = 'N';
        }
        $mute = $mute ? 'Y' : 'N';

        return "yt:$videoId:$startSec:$endSec:$mute";
    }
    
    public static function videoCanvas($canvasData)
    {
        return 'vcan:' . base64_encode(hash('sha1', $canvasData, true));
    }
}

