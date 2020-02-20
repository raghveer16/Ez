<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category\Loader;
use EzAd\Bot\Category\Category;
use EzAd\Bot\Category\CategoryStorageInterface;
use EzAd\Bot\Category\CategorySyncResult;
use EzAd\Bot\RobotConstants;
use Goutte\Client;

/**
 * Class AbstractCategoryLoader
 * @package EzAd\Bot\Category\Loader
 */
abstract class AbstractCategoryLoader
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var CategoryStorageInterface
     */
    protected $storage;

    public function __construct($domain, CategoryStorageInterface $storage)
    {
        $this->domain = $domain;
        $this->storage = $storage;
    }

    /**
     * Loads remote categories (via loadCategories) and compares them with local categories from storage.
     *
     * @return CategorySyncResult
     */
    public function sync()
    {
        $old = $this->storage->getCategories($this->domain);
        $new = $this->loadCategories();

        $oldList = [];
        $newList = [];
        foreach ( $old as $oldCat ) {
            $oldList[$oldCat->getUniqueName()] = $oldCat;
        }
        foreach ( $new as $newCat ) {
            $newList[$newCat->getUniqueName()] = $newCat;
        }

        $added = array_diff_key($newList, $oldList);
        $removed = array_diff_key($oldList, $newList);

        // also propogate added categories up to their local root, but signify that they shouldn't be added
        $skip = [];
        foreach ( $added as $ac ) {
            while ( $p = $ac->getParent() ) {
                $uname = $p->getUniqueName();
                if ( isset($oldList[$uname]) ) {
                    $ac->setParent($oldList[$uname]);
                    $oldList[$uname]->addChild($ac);
                    $added[$uname] = $oldList[$uname];
                    $skip[] = $uname;
                }
                //$added[$p->getUniqueName()] = $p;
                $ac = $p;
            }
        }

        //var_dump($added, $removed, $skip); exit;

        return new CategorySyncResult($added, $removed, $skip);
    }

    /**
     * Shortcut for calling sync() and then apply($storage) on the sync result.
     *
     * @return CategorySyncResult
     */
    public function syncAndFlush()
    {
        $result = $this->sync();
        $result->apply($this->storage);
        return $result;
    }

    public function getClient()
    {
        if ( $this->client === null ) {
            $this->client = new Client();
            $this->client->setHeader('User-Agent', RobotConstants::USER_AGENT);
        }
        return $this->client;
    }

    /**
     * @return Category[]
     */
    abstract public function loadCategories();

    /**
     * @param $domain
     * @return boolean
     */
    abstract public function matches($domain);
}
