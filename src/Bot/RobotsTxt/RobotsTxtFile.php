<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\RobotsTxt;
use EzAd\Bot\RobotConstants;

/**
 * Represents a robots.txt file.
 *
 * @package EzAd\Bot\RobotsTxt
 */
class RobotsTxtFile
{
    private $botName;

    private $sitemapUrls = [];
    private $disallowPatterns = [];

    /**
     * Crawl delay between pages, in milliseconds.
     * @var int
     */
    private $crawlDelay = 1000;

    /**
     * Creates the model for the robots.txt file.
     *
     * @param string $contents The contents of the file.
     * @param string $botName The name of the bot, rules are pulled for the wildcard useragent and this one.
     */
    public function __construct($contents, $botName = RobotConstants::USER_AGENT_SHORT)
    {
        $this->botName = $botName;
        $this->parse($contents);
    }

    private function parse($contents)
    {
        $contents = strtolower($contents);
        $lines = preg_split('/\r?\n/', $contents, -1, PREG_SPLIT_NO_EMPTY);
        $trackPerms = false;

        foreach ( $lines as $line ) {
            $line = trim($line);
            if ( empty($line) || $line[0] == '#' ) {
                continue;
            }

            list($directive, $value) = preg_split('/\s*:\s*/', $line, 2);

            switch ( $directive ) {
                case 'sitemap':
                    $this->sitemapUrls[] = $value;
                    break;
                case 'user-agent':
                    if ( $value == '*' || $value == $this->botName ) {
                        $trackPerms = true;
                    } else {
                        $trackPerms = false;
                    }
                    break;
                case 'disallow':
                    if ( $trackPerms ) {
                        $this->disallowPatterns[] = $value;
                    }
                    break;
                case 'crawl-delay':
                    if ( $trackPerms ) {
                        $this->crawlDelay = max($this->crawlDelay, (int)($value * 1000));
                    }
                    break;
            }
        }
    }

    /**
     * Checks if the given URL is allowed according to the robots.txt. Extracts the path and query and compares
     * it to the list of disallowed URLs.
     *
     * @param $url
     * @return bool
     */
    public function isUrlAllowed($url)
    {
        $parts = parse_url($url);
        $request = strtolower($parts['path'] . '?' . $parts['query']);

        foreach ( $this->disallowPatterns as $patt ) {
            if ( strpos($request, $patt) === 0 ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSitemapUrls()
    {
        return $this->sitemapUrls;
    }

    /**
     * @return int
     */
    public function getCrawlDelay()
    {
        return $this->crawlDelay;
    }
}
