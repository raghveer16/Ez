<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

/**
 * Class CategorySyncResult
 * @package EzAd\Bot\Category
 */
class CategorySyncResult
{
    /**
     * @var Category[]
     */
    private $addedCategories;

    /**
     * @var Category[]
     */
    private $removedCategories;

    /**
     * @var array
     */
    private $skipCategories;

    /**
     * @var array
     */
    private $duplicateTracker = [];

    /**
     * @param array $added
     * @param array $removed
     * @param array $skip
     */
    public function __construct(array $added, array $removed, array $skip = [])
    {
        $this->addedCategories = $added;
        $this->removedCategories = $removed;
        $this->skipCategories = $skip;
    }

    /**
     * Applies the added/removed category changes to the category storage.
     *
     * @param CategoryStorageInterface $storage
     */
    public function apply(CategoryStorageInterface $storage)
    {
        $this->duplicateTracker = [];
        $this->removeRecursive($this->removedCategories, $storage);

        // add top level categories first to set their IDs
        $roots = CategoryUtils::getRoots($this->addedCategories);
        $this->duplicateTracker = [];
        $this->addRecursive($roots, $storage);
    }

    /**
     * @param Category[] $categories
     * @param CategoryStorageInterface $storage
     */
    private function removeRecursive(array $categories, CategoryStorageInterface $storage)
    {
        foreach ( $categories as $removed ) {
            if ( !isset($this->duplicateTracker[$removed->getUniqueName()]) ) {
                $storage->removeCategory($removed);
                $this->duplicateTracker[$removed->getUniqueName()] = true;
            }

            if ( $removed->hasChildren() ) {
                $this->removeRecursive($removed->getChildren(), $storage);
            }
        }
    }

    /**
     * @param Category[] $categories
     * @param CategoryStorageInterface $storage
     */
    private function addRecursive(array $categories, CategoryStorageInterface $storage)
    {
        foreach ( $categories as $category ) {
            // don't add if we're skipping this category, or if this category was never meant to be added
            $uname = $category->getUniqueName();
            if ( !in_array($uname, $this->skipCategories) && isset($this->addedCategories[$uname])
                && !isset($this->duplicateTracker[$uname]) ) {
                $storage->addCategory($category);
                $this->duplicateTracker[$uname] = true;
            }

            if ( $category->hasChildren() ) {
                $this->addRecursive($category->getChildren(), $storage);
            }
        }
    }

    /**
     * @return Category[]
     */
    public function getAddedCategories()
    {
        return $this->addedCategories;
    }

    /**
     * @return Category[]
     */
    public function getRemovedCategories()
    {
        return $this->removedCategories;
    }
}
