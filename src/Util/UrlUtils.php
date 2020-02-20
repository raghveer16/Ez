<?php

/*
 * EZ-AD Lib
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util;

/**
 * Class UrlUtils
 * @package EzAd\Util
 */
class UrlUtils
{
    static public function normalizePath($urlPath)
    {
        $pathParts = explode('/', $urlPath);
        $newParts = array();
        for ( $i = 0; $i < count($pathParts); $i++ ) {
            if ( $pathParts[$i] == '..' && count($newParts) > 0 ) {
                array_pop($newParts);
            } else {
                $newParts[] = $pathParts[$i];
            }
        }

        return implode('/', $newParts);
    }
}
