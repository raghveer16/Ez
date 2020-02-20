<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

/**
 * Class CategoryUtils
 * @package EzAd\Bot\Category
 */
class CategoryUtils
{
    /**
     * Resolves all categories with numeric parents to have actual Category objects as parents.
     *
     * @param Category[] $categories Indexed by ID.
     */
    public static function resolveParents(array &$categories)
    {
        // resolve the parents
        foreach ( $categories as $cat ) {
            $parent = $cat->getParent();
            if ( is_numeric($parent) ) {
                if ( !isset($categories[$parent]) ) {
                    $cat->setParent(null);
                } else {
                    $cat->setParent($categories[$parent]);
                    $categories[$parent]->addChild($cat);
                }
            }
        }
    }

    /**
     * Organize a category list with resolved parents into a hierarchy. Should be fairly
     * simple, parent/child relationships are already set when parents are resolved.
     *
     * @param Category[] $categories List of the top-level categories.
     * @return Category[]
     */
    public static function getRoots(array $categories)
    {
        $top = [];
        foreach ( $categories as $category ) {
            if ( $category->getParent() === null ) {
                $top[] = $category;
            }
        }

        return $top;
    }

    /**
     * @param Category[] $categories
     */
    public static function sortByName(array &$categories)
    {
        usort($categories, function($a, $b) {
            /** @var Category $a */
            /** @var Category $b */
            return strcmp($a->getName(), $b->getName());
        });
    }
}
