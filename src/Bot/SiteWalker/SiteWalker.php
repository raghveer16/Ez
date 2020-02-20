<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\SiteWalker;

/**
 * A site walker contains a hierarchical set of pages that define how to traverse a website.
 *
 * @package EzAd\Bot\SiteWalker
 */
class SiteWalker
{
    /**
     * @var Page
     */
    private $rootPage;

    /**
     * Returns a "root" page for the walker to start at, given the path to the page.
     *
     * e.g. $rootPage = $walker->start('/index.php');
     *
     * @param $path
     * @return \EzAd\Bot\SiteWalker\Page
     */
    public function start($path)
    {
        $this->rootPage = Page::makeRoot($path);
        return $this->rootPage;
    }

    public function getRootPage()
    {
        return $this->rootPage;
    }

    public function getPage($position)
    {
        if ( empty($position) ) {
            return $this->rootPage;
        }

        $split = explode('/', $position);
        $page = $this->rootPage;
        foreach ( $split as $pos ) {
            if ( $pos >= count($page) ) {
                return null;
            }
            $page = $page->getChildren()[$pos];
        }

        return $page;
    }
}
