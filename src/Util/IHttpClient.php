<?php
/**
 * Created by PhpStorm.
 * User: stevenh
 * Date: 2/8/18
 * Time: 4:51 PM
 */

namespace EzAd\Util;


interface IHttpClient
{
    public function get($url, array $options = []);

    public function head($url, array $options = []);

    public function post($url, array $options = []);
}