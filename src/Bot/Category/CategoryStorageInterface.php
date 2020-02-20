<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

/**
 * Interface CategoryStorageInterface
 * @package EzAd\Bot\Category
 */
interface CategoryStorageInterface
{
    /**
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category);

    /**
     * @param Category $category
     * @return void
     */
    public function removeCategory(Category $category);

    /**
     * @param $domain
     * @return Category[]
     */
    public function getCategories($domain);

    /**
     * @return Category[]
     */
    public function getAllCategories();
}
