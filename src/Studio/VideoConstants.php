<?php

namespace EzAd\Studio;

use EzAd\Util\Geometry\Size;

class VideoConstants
{
    private static $sizeObjects = [];

    const SIZE_NTSC  = '720x480';
    const SIZE_PAL   = '720x576';
    const SIZE_QNTSC = '352x240';
    const SIZE_QPAL  = '352x288';
    const SIZE_SNTSC = '640x480';
    const SIZE_SPAL  = '768x576';
    const SIZE_FILM  = '352x240';
    const SIZE_NTSC_FILM = '352x240';
    const SIZE_SQCIF  = '128x96';
    const SIZE_QCIF   = '176x144';
    const SIZE_CIF    = '352x288';
    const SIZE_4CIF   = '704x576';
    const SIZE_16CIF  = '1408x1152';
    const SIZE_QQVGA  = '160x120';
    const SIZE_QVGA   = '320x240';
    const SIZE_VGA    = '640x480';
    const SIZE_SVGA   = '800x600';
    const SIZE_XGA    = '1024x768';
    const SIZE_UXGA   = '1600x1200';
    const SIZE_QXGA   = '2048x1536';
    const SIZE_SXGA   = '1280x1024';
    const SIZE_QSXGA  = '2560x2048';
    const SIZE_HSXGA  = '5120x4096';
    const SIZE_WVGA   = '852x480';
    const SIZE_WXGA   = '1366x768';
    const SIZE_WSXGA  = '1600x1024';
    const SIZE_WUXGA  = '1920x1200';
    const SIZE_WOXGA  = '2560x1600';
    const SIZE_WQSXGA = '3200x2048';
    const SIZE_WQUXGA = '3840x2400';
    const SIZE_WHSXGA = '6400x4096';
    const SIZE_WHUXGA = '7680x4800';
    const SIZE_CGA    = '320x200';
    const SIZE_EGA    = '640x350';
    const SIZE_HD480  = '852x480';
    const SIZE_HD720  = '1280x720';
    const SIZE_HD1080 = '1920x1080';
    const SIZE_2K     = '2048x1080';
    const SIZE_2KFLAT  = '1998x1080';
    const SIZE_2KSCOPE = '2048x858';
    const SIZE_4K      = '4096x2160';
    const SIZE_4KFLAT  = '3996x2160';
    const SIZE_4KSCOPE = '4096x1716';
    const SIZE_NHD     = '640x360';
    const SIZE_HQVGA   = '240x160';
    const SIZE_WQVGA   = '400x240';
    const SIZE_FWQVGA  = '432x240';
    const SIZE_HVGA    = '480x320';
    const SIZE_QHD     = '960x540';

    public static function getSize($size)
    {
        if ( !isset(self::$sizeObjects[$size]) ) {
            self::$sizeObjects[$size] = Size::fromWxH($size);
        }
        return self::$sizeObjects[$size];
    }

    const RATE_NTSC = '30000/1001';
    const RATE_PAL = '25/1';
    const RATE_QNTSC = '30000/1001';
    const RATE_QPAL = '25/1';
    const RATE_SNTSC = '30000/1001';
    const RATE_SPAL = '25/1';
    const RATE_FILM = '24/1';
    const RATE_NTSC_FILM = '24000/1001';
}
