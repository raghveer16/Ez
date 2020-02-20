<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

/**
 * Class Category
 * @package EzAd\Bot\Category
 */
class Category
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var Category|int
     */
    private $parent;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var Category[]
     */
    private $children = [];

    private static $newCounter = 0;

    /**
     * @param string $domain
     * @param Category $parent
     * @param string $name
     * @param string $url
     * @return Category
     */
    public static function makeNew($domain, $parent, $name, $url)
    {
        return new Category(++self::$newCounter, $domain, $parent, $name, $url);
    }

    /**
     * @param int $id
     * @param string $domain
     * @param Category|int $parent
     * @param string $name
     * @param string $url
     */
    public function __construct($id, $domain, $parent, $name, $url)
    {
        $this->id = $id;
        $this->domain = $domain;
        $this->parent = $parent;
        $this->name = $name;
        $this->url = $url;
    }

    /**
     * Returns a domain-unique identifiable name based on this name and all parents names.
     *
     * @return string
     */
    public function getUniqueName()
    {
        return $this->name . ($this->parent ? ('|~|' . $this->parent->getUniqueName()) : '');
    }

    /**
     * @param string $sep
     * @return string
     */
    public function getIdString($sep = ',')
    {
        return ($this->parent ? ($this->parent->getIdString() . $sep) : '') . $this->id;
    }

    /**
     * @param Category $other
     * @return bool
     */
    public function equals(Category $other)
    {
        return $this->getUniqueName() === $other->getUniqueName();
        /*
        // both top-level, just check names
        if ( $this->parent === null && $other->parent === null ) {
            return $this->name === $other->name;
        }

        // since we handle "both are null" above, if this condition succeeds then one is null while the other is not
        if ( $this->parent === null || $other->parent === null ) {
            return false;
        }

        // both have parents (other 3 cases handled above), compare names, and bubble up to parents
        return $this->name === $other->name && $this->parent->equals($other->parent);
        */
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return Category|int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Category|int $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return Category[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Category[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @param Category $category
     */
    public function addChild(Category $category)
    {
        $this->children[] = $category;
    }

    public function hasChildren()
    {
        return count($this->children) > 0;
    }
}
