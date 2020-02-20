<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\SignBuilder\SkuSearch;

use GuzzleHttp\Client;

class Ace implements SkuSearchInterface
{
    /**
     * @param $sku
     * @return array
     */
    public function getProductInfo($sku)
    {
        $client = new Client(['cookies' => true]);

        /*$response = $client->post('http://www.acehardware.com/search/controller.jsp', [
            'headers' => [
                'Referer' => 'http://www.acehardware.com/home/index.jsp',
            ],
            'body' => [
                'kw' => $sku,
                'f' => 'Taxonomy/ACE/19541496',
                'x' => 22,
                'y' => 21,
            ],
        ]);*/

        $response = $client->get(
            "http://www.acehardware.com/search/index.jsp?kwCatId=&kw=$sku&origkw=$sku&f=Taxonomy/ACE/19541496&sr=1", [
                'headers' => [
                    'Referer' => 'http://www.acehardware.com/home/index.jsp',
                ],
            ]
        );

        // on a product page
        if ( strpos($response->getEffectiveUrl(), 'product/index.jsp') === false ) {
            return false;
        }

        $html = $response->getBody();
        $info = [];

        // fileUrl, title, mfr, sku, price

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $xpath = new \DOMXPath($dom);

        // try finding a large image first
        $nodes = $xpath->evaluate('//input[@name="zoom"]');
        if ( $nodes->length > 0 ) {
            $node = $nodes->item(0);
            $info['fileUrl'] = $node->getAttribute('value');
        }

        if ( !isset($info['fileUrl']) ) {
            $nodes = $xpath->evaluate('//img[@id="mainProdImage"]');
            if ( $nodes->length > 0 ) {
                $img = $nodes->item(0);
                $info['fileUrl'] = $img->getAttribute('src');
            }
        }

        $nodes = $xpath->evaluate('//h2[@class="prodC1"]');
        if ( $nodes->length > 0 ) {
            $info['title'] = $nodes->item(0)->nodeValue;
        }

        $info['mfr'] = '';

        if ( preg_match('/Item no:\s*(\d+)/', $html, $m) ) {
            $info['sku'] = $m[1];
        } else {
            $info['sku'] = $sku;
        }

        if ( preg_match('/prodPrice=\'([0-9.]+)\'/', $html, $m) ) {
            $info['price'] = (float) $m[1];
        }

        return $info;
    }
}
