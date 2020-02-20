<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\SignBuilder\SkuSearch;

/**
 * Class TrueValue
 * @package EzAd\SignBuilder\SkuSearch
 */
class TrueValue implements SkuSearchInterface
{
    private static $url = 'http://www.truevalue.com/catalog/search.cmd?keyword=%s';

    /**
     * @param $sku
     * @return array
     */
    public function getProductInfo($sku)
    {
        $client = new \GuzzleHttp\Client();
        $html = (string) $client->get(sprintf(self::$url, urlencode($sku)))->getBody();

        $found = preg_match_all(
            '#http://www\.truevalue\.com/assets/product_images/styles/xlarge/([0-9-]+)\.jpg#',
            $html, $matches);

		if ( STEVEN ) {
			//var_dump($found, $matches);
			//exit;
		}

        if ( $found ) {
            $first = $matches[0][0];
            $info = array(
                'fileUrl' => str_replace('/large/', '/xlarge/', $first),
				'sku' => $sku,
            );

            // try to match title
            if ( preg_match('/<h1 class="product-name">(.+?)<\/h1>/s', $html, $titleInfo) ) {
                $info['title'] = trim(html_entity_decode($titleInfo[1]));
            }

            if ( !$info['title'] && preg_match('/<title>(.+?)<\/title>/s', $html, $titleInfo) ) {
                $info['title'] = $titleInfo[1];
            }

            // try to match mfr, sku
            if ( preg_match('/<div class="item-no">(.+?)<\/div>/s', $html, $itemInfo) ) {
                $itemInfo[1] = trim(str_replace('&nbsp;', '', $itemInfo[1]));
                $split = explode('|', $itemInfo[1]);
                if ( count($split) == 3 ) {
                    $info['mfr'] = $split[0];
                    $info['sku'] = str_replace('item #', '', $split[2]);
                }
            }

            // try to match price
            if ( preg_match('/<div id="priceContainer">(.+?)<\/div>/s', $html, $priceInfo) ) {
                if ( preg_match('/\$\d+(\.\d{2})?/', $priceInfo[1], $price) ) {
                    $info['price'] = str_replace('$', '', $price[0]);
                }
            }

            return $info;
        }
        return false;
    }
}
