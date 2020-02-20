<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\SignBuilder\SkuSearch;

/**
 * Interface SkuSearchInterface
 * @package EzAd\SignBuilder\SkuSearch
 */
interface SkuSearchInterface
{
    /**
     * @param $sku
     * @return array
     */
    public function getProductInfo($sku);
}
