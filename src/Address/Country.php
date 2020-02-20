<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Address;

/**
 * Convenience wrapper around raw JSON country data.
 *
 * @package EzAd\Address
 */
class Country
{
    /**
     * @var array
     */
    private $rawData = [];

    /**
     * Default data from data/ZZ:
     * "fmt":"%N%n%O%n%A%n%C","require":"AC","upper":"C","zip_name_type":"postal","state_name_type":"province"
     *
     * @var array
     */
    private static $defaults = [
        'fmt' => '%N%n%O%n%A%n%C',
        'require' => 'AC',
        'upper' => 'C',
        'zip_name_type' => 'postal',
        'state_name_type' => 'province',
    ];

    public function __construct(array $data)
    {
        $this->rawData = array_merge(self::$defaults, $data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->rawData['name'];
    }

    /**
     * Validates the given postal code against this country, and optionally state/province.
     *
     * $country = $countries['US'];
     * $country->validatePostalCode('48035'); // true
     * $country->validatePostalCode('48035', 'MI'); // true
     * $country->validatePostalCode('48035', 'CA'); // false
     *
     * @param string $code
     * @param null|string $subKey
     * @return bool
     */
    public function validatePostalCode($code, $subKey = null)
    {
        $zipRegex = $this->rawData['zip'];
        $subZipRegex = false;

        if ( $subKey !== null && ($subKeys = $this->getSubKeys()) && ($subZips = $this->getSubZips()) ) {
            $keyIndex = array_search($subKey, $subKeys);
            if ( $keyIndex !== false && isset($subZips[$keyIndex]) ) {
                $subZipRegex = $subZips[$keyIndex];
            }
        }

        // zipRegex matches the entire string, subZipRegex is a prefix match
        $ok = (bool) preg_match('~^' . $zipRegex . '$~', $code);
        if ( $subZipRegex ) {
            $ok = $ok && preg_match('~^' . $subZipRegex . '~', $code);
        }
        return $ok;
    }

    /**
     * Validates the given address by the requirements defined in this country.
     *
     * @param Address $address
     * @return bool
     */
    public function validateAddress(Address $address)
    {
        /*
         * Fields described by Google:
         * N – Name
         * O – Organisation
         * A – Street Address Line(s)
         * D – Dependent locality (may be an inner-city district or a suburb)
         * C – City or Locality
         * S – Administrative area such as a state, province, island etc
         * Z – Zip or postal code
         * X – Sorting code
         */

        $req = $this->rawData['require'];
        $ok = true;
        for ( $i = 0; $i < strlen($req); $i++ ) {
            $ok = $ok && !empty($address->getProperty($req[$i]));
        }

        if ( strpos($req, 'Z') !== false ) {
            $ok = $ok && $this->validatePostalCode($address->getPostalCode(), $address->getAdminArea());
        }

        return $ok;
    }

    /**
     * Formats the given address by the rules defined in this country.
     *
     * @param Address $address
     * @return string
     */
    public function formatAddress(Address $address)
    {
        $format = $this->getFormat();

        // adding a comma seems more correct...
        if ( $this->rawData['key'] == 'US' ) {
            $format = str_replace('%C', '%C,', $format);
        }

        $replace = [];
        foreach ( ['N', 'O', 'A', 'D', 'C', 'S', 'Z', 'X'] as $code ) {
            $replace['%' . $code] = $address->getProperty($code);
        }
        $format = str_replace(array_keys($replace), array_values($replace), $format);

        $text = '';
        foreach ( explode('%n', $format) as $line ) {
            $line = trim($line);
            if ( !empty($line) ) {
                $text .= "$line\n";
            }
        }

        return rtrim($text);
    }

    public function getFormat()
    {
        return isset($this->rawData['lfmt']) ? $this->rawData['lfmt'] : $this->rawData['fmt'];
    }

    /**
     * Returns the name of the postal code type: zip or postal.
     *
     * @return string
     */
    public function getPostalType()
    {
        return $this->rawData['zip_name_type'];
    }

    /**
     * Returns the name of the state type: state, province, parish, etc.
     *
     * @return string
     */
    public function getStateType()
    {
        return $this->rawData['state_name_type'];
    }

    /**
     * Returns true if this country has sub-states.
     *
     * @return bool
     */
    public function hasStates()
    {
        return isset($this->rawData['sub_keys']);
    }

    /**
     * Returns the ISO codes or keys of this country's sub states (if any).
     * For example, the US would return AK, AL, CA, etc.
     *
     * @return array
     */
    public function getSubKeys()
    {
        if ( !$this->hasStates() ) {
            return [];
        }

        return explode('~', isset($this->rawData['sub_isoids'])
            ? $this->rawData['sub_isoids']
            : $this->rawData['sub_keys']);
    }

    /**
     * Returns the names of this country's sub states (if any).
     *
     * @return array
     */
    public function getSubNames()
    {
        if ( !$this->hasStates() ) {
            return [];
        }

        return explode('~', isset($this->rawData['sub_lnames'])
            ? $this->rawData['sub_lnames']
            : $this->rawData['sub_names']);
    }

    /**
     * Returns the list of zip code prefix regexps for each sub-state (if any). Probably not useful outside
     * of this class, use validatePostalCode instead.
     *
     * @return array
     */
    public function getSubZips()
    {
        if ( !$this->hasStates() || !isset($this->rawData['sub_zips']) ) {
            return [];
        }

        return explode('~', $this->rawData['sub_zips']);
    }

    /**
     * Returns a map of state code => name.
     *
     * Ex:
     * $countries = CountryData::instance();
     * print_r($countries['US']->getStates());
     * -> array('AL' => 'Alabama', 'AK' => 'Alaska', ...)
     *
     * @return array
     */
    public function getStates()
    {
        $keys = $this->getSubKeys();
        $values = $this->getSubNames();
        return array_combine($keys, $values);
    }
}
