<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;
use Doctrine\DBAL\Connection;

/**
 * Class DatabaseStorage
 * @package EzAd\Bot\Category
 */
class DatabaseStorage implements CategoryStorageInterface
{
    private $db;

    /**
     * @param $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category)
    {
        $parent = $category->getParent();
        $this->db->insert('product_categories', [
            'domain' => $category->getDomain(),
            'parent' => $parent ? $parent->getId() : 0,
            'name' => $category->getName(),
            'url' => $category->getUrl(),
        ]);

        $category->setId($this->db->lastInsertId());
    }

    /**
     * @param Category $category
     * @return void
     */
    public function removeCategory(Category $category)
    {
        if ( $category->getId() ) {
            $this->db->delete('product_categories', ['id' => $category->getId()]);
        }
    }

    /**
     * @param $domain
     * @return Category[]
     */
    public function getCategories($domain)
    {
        // build a list of categories first, with numeric parents, then resolve the hierarchy later.

        $rs = $this->db->executeQuery('SELECT * FROM product_categories WHERE domain = ?', [$domain]);
        /** @var Category[] $categories */
        $categories = [];
        while ( $c = $rs->fetch() ) {
            $categories[$c['id']] = new Category($c['id'], $c['domain'], $c['parent'], $c['name'], $c['url']);
        }

        CategoryUtils::resolveParents($categories);
        return $categories;
    }

    /**
     * @return Category[]
     */
    public function getAllCategories()
    {
        $rs = $this->db->executeQuery('SELECT * FROM product_categories');
        $categories = [];
        while ( $c = $rs->fetch() ) {
            $categories[] = new Category($c['id'], $c['domain'], $c['parent'], $c['name'], $c['url']);
        }

        return $categories;
    }
}