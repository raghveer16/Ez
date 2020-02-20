<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Category;

use EzAd\Bot\Extractor\ProductExtractorInterface;
use EzAd\Bot\Robot;
use EzAd\Util\IteratorUtils;

/**
 * Class CategoryRobot
 * @package EzAd\Bot\Category
 */
class CategoryRobot extends Robot
{
    /**
     * @var Category[]
     */
    private $categories;

    /**
     * @var string
     */
    private $productAnchorSelector = '';

    /**
     * @var string
     */
    private $pageUrlSelector = '';

    /**
     * @var int
     */
    private $categoryPosition = 0;

    /**
     * @var \SplQueue
     */
    private $categoryQueue;

    /**
     * @var \SplQueue[]
     */
    private $productUrlQueue = [];

    /**
     * @var bool
     */
    private $leafCategoriesOnly = true;

    /**
     * @param string $domain
     * @param ProductExtractorInterface $extractor
     * @param array $rootCategories
     */
    public function __construct($domain, ProductExtractorInterface $extractor, array $rootCategories)
    {
        parent::__construct($domain, $extractor);
        $this->categories = $rootCategories;
    }

    public function start()
    {
        $this->buildProductQueue();

        // productUrlQueue now contains a map of ["cat1,cat2,catX" => ["product", "url", "list"]]
        $this->doProductQueue();
    }

    private function buildProductQueue()
    {
        if ( $this->categoryQueue === null ) {
            $this->categoryQueue = new \SplQueue();
            $catIterator = new CategoryIterator($this->categories);

            if ( $this->leafCategoriesOnly ) {
                $iterator = new \RecursiveIteratorIterator($catIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
                foreach ( $iterator as $category ) {
                    $this->categoryQueue->enqueue($category);
                }
            } else {
                foreach ( IteratorUtils::postOrderDFS($catIterator) as $category ) {
                    $this->categoryQueue->enqueue($category);
                }
            }
        }

        // possibly just do leaf nodes, or make it configurable. otherwise we'll be hitting a TON of duplicate
        // products as we do a sub-category, then hit it again in the category.

        /** @var Category $category */
        while ( $this->categoryQueue->count() > 0 ) {
            $category = $this->categoryQueue->dequeue();
            $catUrl = $category->getUrl();
            $idString = $category->getIdString();

            $this->logger->debug("Loading category page: Ids = $idString; Url = $catUrl");
            $html = $this->loadPageContent($catUrl);
            if ( empty($html) ) {
                continue;
            }

            $this->queueCurrentProductUrls($idString);
            //$this->logger->debug("Cat ID List: " . $category->getIdString());

            if ( $this->pageUrlSelector ) {
                $pageLinks = $this->domCrawler->filter($this->pageUrlSelector)->links();
                $uniqueUris = [];
                foreach ( $pageLinks as $link ) {
                    $pageUrl = $link->getUri();

                    if ( isset($uniqueUris[$pageUrl]) || $this->robotProfile->shouldSkipPageUrl($pageUrl) ) {
                        continue;
                    }
                    $uniqueUris[$pageUrl] = true;

                    $this->pause();
                    $this->logger->debug("Loading category page: Ids = $idString; Url = $pageUrl");
                    $html = $this->loadPageContent($pageUrl);
                    if ( !empty($html) ) {
                        //$this->logger->debug("Cat ID List: " . $category->getIdString());
                        $this->queueCurrentProductUrls($category->getIdString());
                    }
                }
            }

            $this->categoryPosition++;
            $this->robotProfile->saveRobotState($this);

            $this->pause();
        }
    }

    private function doProductQueue()
    {
        while ( count($this->productUrlQueue) > 0 ) {
            reset($this->productUrlQueue);
            /** @var \SplQueue $productUrls */
            list($catList, $productUrls) = each($this->productUrlQueue);
            $this->logger->debug('Starting category set: ' . $catList . ' with ' . count($productUrls) . ' urls');

            while ( $productUrls->count() > 0 ) {
                $url = $productUrls->dequeue(); // use top() and dequeue later?
                $this->logger->debug('URL: ' . $url);
                $html = $this->loadPageContent($url);
                if ( !empty($html) ) {
                    $product = $this->productExtractor->extractProductInfo($this->domCrawler);
                    $product->setCategories(array_map('intval', explode(',', $catList)));
                    $product->setUrl($url);
                    $product->setDomain($this->domain);

                    $this->robotProfile->handleNewProduct($this, $product);
                }
                $this->pause();
            }

            // array_shift will fuck up the catlist keys with one category (aka one number)
            //array_shift($this->productUrlQueue);
            unset($this->productUrlQueue[$catList]);
            $this->robotProfile->saveRobotState($this);
        }
    }

    private function queueCurrentProductUrls($categoryIds)
    {
        if ( !isset($this->productUrlQueue[$categoryIds]) ) {
            $this->productUrlQueue[$categoryIds] = new \SplQueue();
        }

        $productLinks = $this->domCrawler->filter($this->productAnchorSelector)->links();
        foreach ( $productLinks as $link ) {
            $productUrl = $link->getUri();
            if ( !$this->visitedUrls->maybeExists($productUrl) ) {
                //$this->visitedUrls->put($productUrl); // try not using this for CategoryRobot
                $this->productUrlQueue[$categoryIds]->enqueue($productUrl);
            }
        }
    }

    /**
     * @param string $productAnchorSelector
     */
    public function setProductAnchorSelector($productAnchorSelector)
    {
        $this->productAnchorSelector = $productAnchorSelector;
    }

    /**
     * @param string $pageUrlSelector
     */
    public function setPageUrlSelector($pageUrlSelector)
    {
        $this->pageUrlSelector = $pageUrlSelector;
    }

    /**
     * @return array
     */
    public function saveState()
    {
        $data = [
            'type' => __CLASS__,
            'visitedUrls' => $this->visitedUrls,
            'categoryQueue' => $this->categoryQueue,
            'queue' => $this->productUrlQueue,
        ];

        return $data;
    }

    /**
     * @param array $data
     * @return CategoryRobot
     */
    public function restoreFromState(array $data)
    {
        $this->categoryQueue = $data['categoryQueue'];
        $this->productUrlQueue = $data['queue'];
        $this->visitedUrls = $data['visitedUrls'];
    }

    /**
     * @return boolean
     */
    public function getLeafCategoriesOnly()
    {
        return $this->leafCategoriesOnly;
    }

    /**
     * @param boolean $leafCategoriesOnly
     */
    public function setLeafCategoriesOnly($leafCategoriesOnly)
    {
        $this->leafCategoriesOnly = $leafCategoriesOnly;
    }
}
