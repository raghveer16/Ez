<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\SignBuilder\SkuSearch;
use EzAd\EZ;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

/**
 * Class Orgill
 * @package EzAd\SignBuilder\SkuSearch
 */
class Orgill implements SkuSearchInterface
{
    /**
     * @var Client
     */
    private $client;

    public static $url = 'http://orgill.com/index.aspx?QType=11&QOption=7&sku=%s';//&qsPallet=1';

    // large picture is http://orgill.com/Pages/ViewLargeImage.aspx?SKU=XXX
    // small picture is in <img id="ctl00_cphContent_ctl00_ctl06_ctl01_Image1">
    //
    // title inside of <span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblInvoiceDesc"><span ...>XXX</span></span>
    // mfr inside of <span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblVendor1">XXX</span>
    // sku inside of <span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblSKUUPC"><span ...>XXX</span></span> - maybe strip &nbsp;Y
    // price inside of <span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblPRICE">XXX</span> - looks like USD X.YY/EA

    // title also inside of
    // <span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblHeading"><SPAN style='font-family: Arial;font-size: 17px; font-weight: 600; color: #9a0000;text-transform:capitalize;'>curved claw hammers</SPAN>&nbsp;&nbsp;<SPAN style='font-family: Arial; font-size: 17px; font-weight: 600; color: #000000;text-transform:capitalize;'>16oz claw hammer wood</SPAN></span>
    //

    /**
     * @param $sku
     * @return array
     */
    public function getProductInfo($sku)
    {
        // need to load the homepage first or else it'll break
        $client = $this->getClient();
        $client->get('http://orgill.com/index.aspx?QType=100150', [
            'headers' => [
                'Referer' => 'http://orgill.com/',
            ],
        ]);
        usleep(200000); // 0.2s

        // load the product page
        $sku = urlencode($sku);
        $productUrl = sprintf(self::$url, $sku);
        $response = $client->get($productUrl, [
            'headers' => [
                'Referer' => 'http://orgill.com/index.aspx?QType=100150',
            ],
        ]);
        $html = $response->getBody();

        if ( strpos($html, 'No Sku found') === false ) {
            $info = [];
            if ( strpos($html, 'ViewLargeImage.aspx?SKU=') ) {
                $info['fileUrl'] = 'http://orgill.com/Pages/ViewLargeImage.aspx?SKU=' . $sku;
            } else if ( preg_match('#https?://images\.orgill\.com/WebSmall/[^/]+/[^/]+?\.jpg#', $html, $m) ) {
				// http://www.orgill.com/Pages/ViewLargeImage.aspx?IMGURL=path/file.jpg
				$info['fileUrl'] = preg_replace('#https?://images\.orgill\.com/WebSmall/#', 
					'http://www.orgill.com/Pages/ViewLargeImage.aspx?IMGURL=', $m[0]);
            }

            if ( preg_match('#<span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblHeading2">(.+?)</span>#', $html, $m) ) {
                // use inner span
                $m[1] = trim($m[1]);
                $span2 = strpos($m[1], '<SPAN');
                if ( $span2 !== false ) {
                    $span2end = strpos($m[1], '>', $span2);
                    if ( $span2end ) {
                        $span2end++;
                        $span2close = strpos($m[1], '</SPAN>', $span2end);
                        if ( $span2close ) {
                            $info['title'] = ucwords(substr($m[1], $span2end, $span2close - $span2end));
                        }
                    }
                }
            }

            if ( !isset($info['title']) 
                && preg_match('#<span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblInvoiceDesc">(.+?)</span>#', $html, $m) ) {
                $info['title'] = trim(strip_tags($m[1]));
            }

            if ( preg_match('#<span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblVendor1">(.+?)</span>#', $html, $m) ) {
                $info['mfr'] = trim($m[1]);
            }

            if ( preg_match('#<span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblSKUUPC">(.+?)</span>#', $html, $m) ) {
                $info['sku'] = preg_replace('#&nbsp;[A-Z ]?#', '', trim(strip_tags($m[1])));
            }

            if ( preg_match('#<span id="ctl00_cphContent_ctl00_ctl06_ctl01_lblPRICE">(.+?)</span>#', $html, $m) ) {
                $info['price'] = trim($m[1]);
                $info['price'] = preg_replace('/[^0-9.]/', '', $info['price']);
            }

            // download the file to tmp
            if ( isset($info['fileUrl']) ) {
                $saveto = EZ::get('tmp_path') . '/orgill_dl_' . uniqid() . '.jpg';
                $client->get($info['fileUrl'], [
                    'headers' => [
                        'Referer' => $productUrl,
                    ],
                    'save_to' => $saveto,
                ]);
                $info['fileUrl'] = $saveto;
            }

            return $info;
        }

        return false;
    }

    private function getClient()
    {
        if ( $this->client !== null ) {
            return $this->client;
        }

        // .ASPXAUTH cookie (in the future might need to actually log-in every time)
		$auth = 'E15E8140B95EA5489524300A6DFC2D74111AF69ACD15238CCC36D843111CD52A29C22468B7D9A4FDA02043A235515BC8561F4895C5F355C4C01D16CD953014C5A2466BFFC4E134FC7160C43D3A30639280182CB944A491EFDBA5811FCA945A91FCA294EC36CF20D530156991E2A356F03A9E3742';

		if ( is_file('/home/heyads/www2/scripts/orgill_authcookie.txt') ) {
			$auth = file_get_contents('/home/heyads/www2/scripts/orgill_authcookie.txt');
		}

        $jar = new CookieJar(false, [
            [
                'Name' => '.ASPXAUTH',
                'Value' => $auth,
                'Domain' => 'orgill.com',
                'Max-Age' => 86400,
                'HttpOnly' => true,
            ],
        ]);
        return $this->client = new Client([
            'defaults' => ['cookies' => $jar]
        ]);
    }
}
