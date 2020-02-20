<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util\Pattern;

/**
 * Provides a reusable method to allow for "write-once" properties via setters.
 *
 * @package EzAd\Core\Util\Pattern
 */
trait WriteOnceTrait
{
    private $_writtenProperties = [];

    protected function _checkWrite($property)
    {
        if ( isset($this->_writtenProperties[$property]) ) {
            throw new \RuntimeException(
                "Write-once property '$property' has already been set on class '" . __CLASS__ . '"');
        }
        $this->_writtenProperties[$property] = true;
    }
}
