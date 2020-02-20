<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot;

use EzAd\Bot\Extractor\ProductExtractorInterface;
use EzAd\Bot\Profile\ProfileInterface;
use EzAd\Bot\RobotsTxt\RobotsTxtFile;
use EzAd\Bot\UrlFilter\ChainFilter;
use EzAd\Bot\UrlFilter\DomainFilter;
use EzAd\Bot\UrlFilter\UrlFilterInterface;
use EzAd\Bot\UrlReplacer\ClosureReplacer;
use EzAd\Bot\UrlReplacer\UrlReplacerInterface;
use EzAd\Util\BloomFilter;
use Goutte\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DomCrawler\Crawler;

class Robot
{
    /**
     * @var \Goutte\Client
     */
    private $client;

    /**
     * @var \EzAd\Util\BloomFilter
     */
    protected $visitedUrls;

    /**
     * @var int
     */
    private $visitedCount = 0;

    /**
     * @var \SplQueue
     */
    protected $queue;

    /**
     * @var ChainFilter
     */
    private $urlFilter;

    /**
     * @var UrlReplacerInterface[]
     */
    private $urlReplacers = [];

    /**
     * Delay between loading pages is crawl delay + 0 to $extraDelayMax ms. Crawl delay defaults to 1000ms.
     *
     * @var int
     */
    private $extraDelayMax = RobotConstants::EXTRA_DELAY_MAX_MS;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var array
     */
    private $startUrls = [];

    /**
     * @var int
     */
    private $startUrlIndex = 0;

    /**
     * @var RobotsTxtFile
     */
    protected $robotsTxt;

    /**
     * The DOM crawler for the current page.
     *
     * @var Crawler
     */
    protected $domCrawler;

    /**
     * @var ProductExtractorInterface
     */
    protected $productExtractor;

    /**
     * @var ProfileInterface
     */
    protected $robotProfile;

    /**
     * @param string $domain
     * @param ProductExtractorInterface $extractor
     */
    public function __construct($domain, ProductExtractorInterface $extractor)
    {
        $this->client = new Client();
        $this->client->setHeader('User-Agent', RobotConstants::USER_AGENT);

        // Bloom filters will always tell you correctly that something exists (so no duplicate URLs).
        // However, it may also tell you something exists when it does not, which can result in URLs being
        // skipped. The chance of this happening with the below parameters is about 1 in 100,000,
        // with 84,000 URLs stored, so on many sites it may never even happen.
        // Plus side: tracking 84K URLs like this takes 512KB of RAM instead of 20MB, 40 times less.
        $this->visitedUrls = new BloomFilter(1 << 21, 1 << 16);

        $this->queue = new \SplQueue();

        $this->domain = $domain;

        $this->urlFilter = new ChainFilter();
        $this->urlFilter->addFilter(new DomainFilter([$domain]), 'domain_whitelist');

        $this->productExtractor = $extractor;

        $this->logger = new NullLogger();
    }

    /**
     * @param UrlFilterInterface $filter
     */
    public function addUrlFilter(UrlFilterInterface $filter)
    {
        $this->urlFilter->addFilter($filter);
    }

    /**
     * @param UrlReplacerInterface $replacer
     */
    public function addUrlReplacer(UrlReplacerInterface $replacer)
    {
        $this->urlReplacers[] = $replacer;
    }

    /**
     * Runs through urlReplacers and performs replacements on the given URL.
     *
     * @param $url
     * @return string
     */
    protected function processReplacements($url)
    {
        foreach ( $this->urlReplacers as $replacer ) {
            $url = $replacer->replace($url);
        }
        return $url;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $urls
     */
    public function setStartUrls(array $urls)
    {
        $this->startUrls = $urls;
        $this->startUrlIndex = 0;
    }

    /**
     * @param string $domain
     */
    public function addAllowedDomain($domain)
    {
        /** @var DomainFilter $filter */
        $filter = $this->urlFilter->findFilter('domain_whitelist');
        $filter->addAllowedDomain($domain);
    }

    /**
     * Start the robot process.
     */
    public function start()
    {
        if ( $this->queue->isEmpty() ) {
            $this->queueUrls($this->startUrls[$this->startUrlIndex]);
        }

        $limit = 500000;
        $timeSince = time();
        $index = 0;
        while ( $this->queue->count() && $index < $limit ) {
            $url = $this->queue->dequeue();
            if ( empty($url) ) {
                continue;
            }

            $this->logger->debug('LOAD: ' . $url);
            $content = $this->loadPageContent($url);

            if ( !empty($content) ) {
                $pageUrls = $this->findUrlsOnPage();
                $this->queueUrls($pageUrls);
                $this->logger->debug('URLS: ' . count($pageUrls));

                // possibly scrape the page
            }

            // save state
            $index++;
            if ( $index % 20 == 0 ) {
                $this->robotProfile->saveRobotState($this);
                $mem = round(memory_get_usage() / 1024 / 1024, 3);
                $qs = $this->queue->count();
                $ds = $this->visitedCount;
                $this->logger->debug("SAVE: $mem MB, $qs Q, $ds D");

                // see if it's been a while since the last prod was added
                $checkTime = 3600;
                if ( time() > $timeSince + $checkTime ) {
                    $this->queue = new \SplQueue();
                    $this->visitedCount = 0;
                    $this->visitedUrls = new BloomFilter(1 << 21, 1 << 16);
                    $this->startUrlIndex++;
                    if ( $this->startUrlIndex >= count($this->startUrls) ) {
                        break;
                    }
                    $this->queueUrls($this->startUrls[$this->startUrlIndex]);
                }
            }

            $this->client->getHistory()->clear(); // don't need this clogging up memory, not using it.
            $this->pause();
        }
    }

    /**
     * Loads page content into $this->domCrawler and updates the given $url parameter
     * if there was a redirect.
     *
     * @param string $url
     * @return string
     */
    protected function loadPageContent(&$url)
    {
        $this->domCrawler = $this->client->request('GET', $url);
        $url = $this->client->getInternalRequest()->getUri();

        $content = $this->client->getInternalResponse()->getContent();
        return empty($content) ? '' : $this->domCrawler->html();
    }

    /**
     * Extracts URLs from the current page. Much cleaner than Wishmaker's robot since Symfony's DOMCrawler
     * component takes care of most of the bullshit.
     *
     * @return mixed
     */
    protected function findUrlsOnPage()
    {
        $allLinks = $this->domCrawler->filter('a[href]')->links();
        $urlList = [];

        foreach ( $allLinks as $link ) {
            $href = $link->getUri();
            if ( stripos($href, 'javascript:') !== false ) {
                continue;
            }
            $href = $this->processReplacements($href);
            if ( $this->visitedUrls->maybeExists($href) ) {
                continue;
            }

            $urlList[$href] = true;
        }

        return $this->urlFilter->filterUrlList(array_keys($urlList));
    }

    /**
     * Adds the given URL(s) to the queue of pages to download.
     *
     * @param $urls
     */
    protected function queueUrls($urls)
    {
        if ( !is_array($urls) ) {
            $urls = array($urls);
        }

        foreach ( $urls as $u ) {
            $this->visitedUrls->put($u);
            $this->queue->enqueue($u);
        }
    }

    /**
     * Pauses for a time equal to the crawl delay plus up to 1 second.
     */
    protected function pause()
    {
        $delay = $this->robotsTxt ? $this->robotsTxt->getCrawlDelay() : 1000;
        $delay += mt_rand(0, $this->extraDelayMax);

        usleep($delay * 1000);
    }

    /**
     * Saves the robot state to a file named after the domain, inside of the save path.
     *
     * @return array
     */
    public function saveState()
    {
        $data = [
            'type' => __CLASS__,
            'domain' => $this->domain,
            'visitedUrls' => $this->visitedUrls,
            'visitedCount' => $this->visitedCount,
            'queue' => $this->queue,
        ];

        return $data;
    }

    /**
     * Restores the Robot state from a file.
     *
     * @param array $data
     * @return Robot
     */
    public function restoreFromState(array $data)
    {
        $this->domain = $data['domain'];
        $this->visitedUrls = $data['visitedUrls'];
        $this->visitedCount = $data['visitedCount'];
        $this->queue = $data['queue'];
    }

    // ------------------------------------------
    // getters/setters, so Profiles can interact
    // ------------------------------------------

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
     * @return ProductExtractorInterface
     */
    public function getProductExtractor()
    {
        return $this->productExtractor;
    }

    /**
     * @param ProductExtractorInterface $productExtractor
     */
    public function setProductExtractor($productExtractor)
    {
        $this->productExtractor = $productExtractor;
    }

    /**
     * @return \SplQueue
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param \SplQueue $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->savePath;
    }

    /**
     * @param string $savePath
     */
    public function setSavePath($savePath)
    {
        $this->savePath = $savePath;
    }

    /**
     * @return int
     */
    public function getStartUrlIndex()
    {
        return $this->startUrlIndex;
    }

    /**
     * @param int $startUrlIndex
     */
    public function setStartUrlIndex($startUrlIndex)
    {
        $this->startUrlIndex = $startUrlIndex;
    }

    /**
     * @return array
     */
    public function getStartUrls()
    {
        return $this->startUrls;
    }

    /**
     * @return ChainFilter
     */
    public function getUrlFilter()
    {
        return $this->urlFilter;
    }

    /**
     * @param ChainFilter $urlFilter
     */
    public function setUrlFilter($urlFilter)
    {
        $this->urlFilter = $urlFilter;
    }

    /**
     * @return UrlReplacer\UrlReplacerInterface[]
     */
    public function getUrlReplacers()
    {
        return $this->urlReplacers;
    }

    /**
     * @param UrlReplacer\UrlReplacerInterface[] $urlReplacers
     */
    public function setUrlReplacers($urlReplacers)
    {
        $this->urlReplacers = $urlReplacers;
    }

    /**
     * @return int
     */
    public function getVisitedCount()
    {
        return $this->visitedCount;
    }

    /**
     * @param int $visitedCount
     */
    public function setVisitedCount($visitedCount)
    {
        $this->visitedCount = $visitedCount;
    }

    /**
     * @return BloomFilter
     */
    public function getVisitedUrls()
    {
        return $this->visitedUrls;
    }

    /**
     * @param BloomFilter $visitedUrls
     */
    public function setVisitedUrls($visitedUrls)
    {
        $this->visitedUrls = $visitedUrls;
    }

    /**
     * @return ProfileInterface
     */
    public function getRobotProfile()
    {
        return $this->robotProfile;
    }

    /**
     * @param ProfileInterface $robotProfile
     */
    public function setRobotProfile($robotProfile)
    {
        $this->robotProfile = $robotProfile;
    }
}
