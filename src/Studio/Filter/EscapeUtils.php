<?php

namespace EzAd\Studio\Filter;

class EscapeUtils
{
    /**
     * Escapes an option of a single filter.
     */
    public static function option($t)
    {
        // backslash, colon, apos
        return addcslashes($t, '\\:\'');
    }

    /**
     * Escapes a filter description.
     */
    public static function description($t)
    {
        // backslash, comma, apos, lbrack, rbrack, semicolon
        return addcslashes($t, '\\,\'[];');
    }

    /**
     * Escapes the entire filter graph.
     */
    public static function graph($t)
    {
        // backslash
        return '"' . addcslashes($t, '\\') . '"';
    }
}
