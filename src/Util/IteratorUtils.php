<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util;

/**
 * Class IteratorUtils
 * @package EzAd\Util
 */
class IteratorUtils
{
    /**
     * Generator that performs a post-order depth-first search on a recursive iterator.
     *
     * @param \RecursiveIterator $it
     * @return \Generator
     */
    public static function postOrderDFS(\RecursiveIterator $it)
    {
        while ( $it->valid() ) {
            if ( $it->hasChildren() ) {
                foreach ( self::postOrderDFS($it->getChildren()) as $el ) {
                    yield $el;
                }
            }
            yield $it->current();
            $it->next();
        }
    }
}
