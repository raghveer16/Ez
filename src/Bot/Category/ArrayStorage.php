<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

/**
 * Dummy class that stores categories into an array. Mainly just for testing.
 *
 * @package EzAd\Bot\Category
 */
class ArrayStorage implements CategoryStorageInterface
{
    /**
     * @var array
     */
    private $categoryData = [];

    /**
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category)
    {
        $d = $category->getDomain();
        if ( !isset($this->categoryData[$d]) ) {
            $this->categoryData[$d] = [];
        }
        $this->categoryData[$d][] = $category;
    }

    /**
     * @param Category $category
     * @return void
     */
    public function removeCategory(Category $category)
    {
        $found = null;
        $d = $category->getDomain();
        if ( isset($this->categoryData[$d]) ) {
            foreach ( $this->categoryData[$d] as $i => $c ) {
                if ( $category->equals($c) ) {
                    $found = $i;
                    break;
                }
            }
        }

        if ( $found !== null ) {
            unset($this->categoryData[$d][$found]);
        }
    }

    /**
     * @param $domain
     * @return Category[]
     */
    public function getCategories($domain)
    {
        return isset($this->categoryData[$domain]) ? $this->categoryData[$domain] : [];
    }

    /**
     * @return Category[]
     */
    public function getAllCategories()
    {
        $flat = [];
        foreach ( $this->categoryData as $lists ) {
            foreach ( $lists as $c ) {
                $flat[] = $c;
            }
        }
        return $flat;
    }
}
