<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Address;

/**
 * Class CountryData
 * @package EzAd\Address
 */
class CountryData implements \ArrayAccess
{
    /**
     * @var array
     */
    private $countries = [];

    /**
     * @var array
     */
    private $countryInstances = [];

    /**
     * @var CountryData
     */
    private static $instance = null;

    /**
     * Returns a singleton instance of the country data.
     *
     * @return CountryData
     */
    public static function instance()
    {
        if ( self::$instance === null ) {
            self::$instance = new CountryData();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->loadCountries();
    }

    private function loadCountries()
    {
        $this->countries = json_decode(file_get_contents(__DIR__ . '/data/countries.json'), true);
    }

    /**
     * Sets a custom country name to override the default.
     *
     * @param string $code
     * @param string $name
     */
    public function setCustomName($code, $name)
    {
        if ( isset($this->countries[$code]) ) {
            $this->countries[$code]['name'] = $name;
        }
    }

    /**
     * Returns a key/value mapping of all raw country data.
     *
     * @return array
     */
    public function all()
    {
        return $this->countries;
    }

    /**
     * Returns raw country data for the given code.
     *
     * @param string $code
     * @return array
     */
    public function raw($code)
    {
        return $this->countries[$code];
    }

    /**
     * Returns a list of all country codes.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->countries);
    }

    /**
     * Returns a country_code => name mapping with the given preferred country codes on top.
     *
     * Ex:
     * <select name="country">
     * <option value="">Select your country</option>
     * <? foreach ( $countryData->options() as $key => $value ) { ?>
     * <option value="<?=$key?>"><?=$name?></option>
     * <? } ?>
     * </select>
     *
     * @param array $preferred
     * @param string $separator
     * @return array
     */
    public function options($preferred = ['US', 'CA', 'GB'], $separator = '---------------')
    {
        $options = [];
        foreach ( $preferred as $code ) {
            $options[$code] = $this->countries[$code]['name'];
        }

        if ( $separator ) {
            $options[''] = $separator;
        }

        foreach ( $this->countries as $code => $data ) {
            if ( !isset($options[$code]) ) {
                $options[$code] = $data['name'];
            }
        }

        return $options;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->countries[$offset]);
    }

    /**
     * @param string $offset
     * @return Country
     * @throws \OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        if ( !isset($this->countries[$offset]) ) {
            throw new \OutOfBoundsException("$offset is not a valid country code");
        }

        if ( !isset($this->countryInstances[$offset]) ) {
            $this->countryInstances[$offset] = new Country($this->countries[$offset]);
        }

        return $this->countryInstances[$offset];
    }

    /**
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Country data is read-only');
    }

    /**
     * @throws \RuntimeException
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Country data is read-only');
    }
}
