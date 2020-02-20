<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

$path = __DIR__ . '/../src/Address/data';
$big = $path . '/countryinfo.txt';

mb_regex_encoding('UTF-8');

$mapping = [];
$lines = file($big);
foreach ( $lines as $line ) {
    $parts = mb_split('=', $line, 2);
    if ( count($parts) != 2 ) {
        echo count($parts), " | $line";
    }

    list($key, $json) = $parts;
    if ( $key == 'data' || $key == 'data/ZZ' || strpos($key, 'examples') === 0 ) {
        continue;
    }

    $key = substr($key, 5);
    if ( mb_strpos($key, '--') !== false ) {
        list($key, $lang) = mb_split('--', $key);

        // if $lang is en, use that, otherwise use the default version
        if ( $lang != 'en' ) {
            continue;
        }
    }

    $split = mb_split('/', $key);
    if ( count($split) > 1 ) {
        continue;
    }

    $k = $split[0];
    $mapping[$k] = json_decode($json, true);

    // process names
    if ( isset($mapping[$k]['name']) ) {
        $name = $mapping[$k]['name'];
        $name = ucwords(strtolower($name));

        $name = preg_replace_callback('/\b(And|Of|The)\b/', function($m) {
            return strtolower($m[1]);
        }, $name);

        $name = preg_replace_callback('/(\(|\.)([a-z])/', function($m) {
            return $m[1] . strtoupper($m[2]);
        }, $name);

        $mapping[$k]['name'] = $name;
    }

    // strip out data we don't need
    unset($mapping[$k]['id']);
    unset($mapping[$k]['zipex']);
    unset($mapping[$k]['posturl']);
    unset($mapping[$k]['sub_zipexs']);
}

// overrides
$mapping['US']['name'] = 'United States of America';
$mapping['HM']['name'] = 'Heard and McDonald Islands';
$mapping['CN']['name'] = 'China';

uasort($mapping, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

echo implode(' ', array_keys($mapping)), "\n";
file_put_contents($path . '/countries.json', json_encode($mapping));


