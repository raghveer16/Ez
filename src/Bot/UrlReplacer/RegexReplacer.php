<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlReplacer;

/**
 * Class RegexReplacer
 * @package EzAd\Bot\UrlReplacer
 */
class RegexReplacer implements UrlReplacerInterface
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string|\Closure
     */
    private $to;

    /**
     * @param string $from
     * @param string|\Closure $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param string $url
     * @return string
     */
    public function replace($url)
    {
        return is_string($this->to)
            ? preg_replace($this->from, $this->to, $url)
            : preg_replace_callback($this->from, $this->to, $url);
    }
}
