<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\SiteWalker;
use Traversable;

/**
 * Class Page
 * @package EzAd\Bot\SiteWalker
 */
class Page implements \Countable, \IteratorAggregate
{
    /**
     * @var Page[]
     */
    private $children = [];

    /**
     * @var Page
     */
    private $parent = null;

    /**
     * @var string
     */
    private $currentPath = '';

    /**
     * @var int
     */
    private $depth = 0;

    /**
     * @var string
     */
    private $position = '';

    /**
     * @var string
     */
    private $linkSelector = '';

    /**
     * Selector for link to the next page, for pagination.
     *
     * @var string
     */
    private $nextPageSelector = '';

    public static function makeRoot($path)
    {
        $page = new Page();
        $page->currentPath = $path;
        $page->depth = 0;

        return $page;
    }

    public function followLinks($selector)
    {
        $subpage = new Page();
        $subpage->linkSelector = $selector;
        $subpage->parent = $this;
        $subpage->depth = $this->depth + 1;
        $subpage->position = (strlen($this->position) > 0 ? ($this->position . '/') : '') . count($this->children);

        $this->children[] = $subpage;
        return $subpage;
    }

    /**
     * @return string
     */
    public function getCurrentPath()
    {
        return $this->currentPath;
    }

    /**
     * @return string
     */
    public function getLinkSelector()
    {
        return $this->linkSelector;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * @return Page[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return Page
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $nextPageSelector
     */
    public function setNextPageSelector($nextPageSelector)
    {
        $this->nextPageSelector = $nextPageSelector;
    }

    public function getNextPageSelector()
    {
        return $this->nextPageSelector;
    }

    /**
     * @return bool
     */
    public function hasPagination()
    {
        return !empty($this->nextPageSelector);
    }

    /**
     * @return \ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->children);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->children);
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
