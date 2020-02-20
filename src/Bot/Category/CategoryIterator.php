<?php

namespace EzAd\Bot\Category;

/**
 * Recursive iterator implementation for product categories.
 *
 * @package EzAd\Bot\Category
 */
class CategoryIterator implements \RecursiveIterator
{
    /**
     * @var Category[]
     */
    private $categories = [];

    /**
     * @var int
     */
    private $position = 0;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function current()
    {
        return $this->categories[$this->position];
    }

    public function next()
    {
        $this->position++;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->categories[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function hasChildren()
    {
        return $this->categories[$this->position]->hasChildren();
    }

    public function getChildren()
    {
        return new CategoryIterator($this->categories[$this->position]->getChildren());
    }
}