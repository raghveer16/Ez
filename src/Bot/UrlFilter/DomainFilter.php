<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class DomainFilter
 * @package EzAd\Bot\UrlFilter
 */
class DomainFilter implements UrlFilterInterface
{
    /**
     * @var array
     */
    private $allowedDomains = [];

    /**
     * @param array $allowedDomains
     */
    public function __construct(array $allowedDomains)
    {
        $this->allowedDomains = $allowedDomains;
    }

    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        $domains = array_flip($this->allowedDomains);
        return array_filter($urls, function($url) use ($domains) {
            $d = parse_url($url, PHP_URL_HOST);
            return isset($domains[$d]);
        });
    }

    /**
     * @return array
     */
    public function getAllowedDomains()
    {
        return $this->allowedDomains;
    }

    /**
     * @param array $allowedDomains
     */
    public function setAllowedDomains($allowedDomains)
    {
        $this->allowedDomains = $allowedDomains;
    }

    /**
     * @param string $domain
     */
    public function addAllowedDomain($domain)
    {
        $this->allowedDomains[] = $domain;
    }
}
