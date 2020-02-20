<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot;
use EzAd\Bot\Extractor\ProductExtractorInterface;
use EzAd\Bot\ProductStore\Product;
use EzAd\Bot\SiteWalker\Page;
use EzAd\Bot\SiteWalker\SiteWalker;
use Symfony\Component\DomCrawler\Link;

/**
 * Class SiteWalkingRobot
 * @package EzAd\Bot
 */
class SiteWalkingRobot extends Robot
{
    /**
     * @var SiteWalker
     */
    private $walker;

    /**
     * Stack of queues.
     *
     * @var array
     */
    private $queueStack = [];

    /**
     * @var Page
     */
    private $currentPage;

    public function __construct($domain, ProductExtractorInterface $extractor, SiteWalker $walker)
    {
        parent::__construct($domain, $extractor);

        $this->walker = $walker;
        $this->currentPage = $walker->getRootPage();

        // initialize queue stack with one queue, containing the root URL.
        $this->queueStack = [
            ['http://' . $this->currentPage->getCurrentPath()]
        ];
    }

    public function start()
    {
        $page = $this->currentPage;
        $depth = $page->getDepth(); //count($this->queueStack) - 1;

        while ( true ) {
            $queue =& $this->queueStack[$depth];
            $nextUrl = array_shift($queue);
            $body = $this->loadPageContent($nextUrl);
            if ( $page->hasChildren() ) {
                foreach ( $page->getChildren() as $index => $sp ) {
                    $newUrls = $this->getUrlsBySelector($sp->getLinkSelector());

                    // if this page has pagination, we need to keep following each page and collect URLs until
                    // we can't find any more pages.
                    if ( $page->hasPagination() ) {
                        $this->collectPaginationUrls($page);
                    }

                    // push a new queue onto the stack with the discovered URLs in this page.
                    // then set the page to the subpage, increase depth, and start at the top of the while loop again.
                    $subqueue = $newUrls;
                    $page = $sp;
                    $this->queueStack[] = $subqueue;
                    $depth++;
                    continue 2;
                }
            } else {
                // product page? no children, so we're at a destination point.

            }

            if ( empty($this->queueStack[$depth]) ) {
                if ( $depth == 0 ) {
                    break;
                } else {
                    $page = $page->getParent();
                    $depth--;
                }
            }
        }
    }

    private function collectPaginationUrls(Page $page)
    {

        $urls = [];
        while ( true ) {
            $nextPageUrls = $this->getUrlsBySelector($page->getNextPageSelector());
            if ( empty($nextPageUrls) ) {
                break;
            }

            $this->loadPageContent($nextPageUrls[0]);
            foreach ( $this->getUrlsBySelector($page->getLinkSelector()) as $url ) {
                $urls[] = $url;
            }
        }

    }

    public function saveState()
    {

    }

    public function restoreFromState($file)
    {

    }

    private function getUrlsBySelector($selector)
    {
        $links = $this->domCrawler->filter($selector)->links();
        $urls = [];
        foreach ( $links as $link ) {
            $urls[] = $link->getUri();
            // do we want to have url replacements, visited url checking, etc. here?
        }
        return $urls;
    }
}
