<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\UrlFilter;

/**
 * Class ChainFilter
 * @package EzAd\Bot\UrlFilter
 */
class ChainFilter implements UrlFilterInterface
{
    /**
     * @var UrlFilterInterface[]
     */
    private $filters;

    /**
     * @var array
     */
    private $indexes = [];

    /**
     * @param UrlFilterInterface[] $filters
     */
    public function __construct(array $filters = [])
    {
        $this->setFilters($filters);
    }

    /**
     * @param array $urls
     * @return array
     */
    public function filterUrlList(array $urls)
    {
        foreach ( $this->filters as $filter ) {
            $urls = $filter->filterUrlList($urls);
        }
        return $urls;
    }

    /**
     * @param UrlFilterInterface $filter
     * @param string $name
     */
    public function addFilter(UrlFilterInterface $filter, $name = '')
    {
        $this->filters[] = $filter;
        if ( $name ) {
            $this->indexes[$name] = count($this->filters) - 1;
        }
    }

    public function findFilter($name)
    {
        if ( !isset($this->indexes[$name]) ) {
            return null;
        }
        return $this->filters[$this->indexes[$name]];
    }

    /**
     * @return UrlFilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param UrlFilterInterface[] $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }
}