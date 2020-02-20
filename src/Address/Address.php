<?php
/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Address;

/**
 * Class Address
 *
 * Fields described by Google:
 * N – Name
 * O – Organisation
 * A – Street Address Line(s)
 * D – Dependent locality (may be an inner-city district or a suburb)
 * C – City or Locality
 * S – Administrative area such as a state, province, island etc
 * Z – Zip or postal code
 * X – Sorting code
 *
 * @package EzAd\Address
 */
class Address
{
    /**
     * The name of the person.
     * @var string
     */
    private $name;

    /**
     * The name of the organization.
     * @var string
     */
    private $organization;

    /**
     * Each line of the street address, up to 3 lines.
     * @var array
     */
    private $streetLines = [];

    /**
     * Dependent locality, like a district or suburb.
     * @var string
     */
    private $locality;

    /**
     * City, town, etc.
     * @var string
     */
    private $city;

    /**
     * Administrative area, like a state, province, or an island.
     * @var string
     */
    private $adminArea;

    /**
     * ZIP or postal code.
     * @var string
     */
    private $postalCode;

    /**
     * Sorting code.
     * @var string
     */
    private $sortingCode;

    /**
     * Gets a property by its Google code character.
     *
     * @param string $code
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getProperty($code)
    {
        switch ( $code ) {
            case 'N': return $this->getName();
            case 'O': return $this->getOrganization();
            case 'A': return $this->getStreetText();
            case 'D': return $this->getLocality();
            case 'C': return $this->getCity();
            case 'S': return $this->getAdminArea();
            case 'Z': return $this->getPostalCode();
            case 'X': return $this->getSortingCode();
            default: throw new \InvalidArgumentException('Invalid property code ' . $code);
        }
    }

    /**
     * @param int $maxLines
     * @param string $joiner
     * @return string
     */
    public function getStreetText($maxLines = 3, $joiner = "\n")
    {
        if ( empty($this->streetLines) ) {
            return '';
        }

        return implode($joiner, array_slice($this->streetLines, 0, $maxLines));
    }

    // aliases for admin area
    public function setState($state) { $this->setAdminArea($state); }
    public function getState() { return $this->getAdminArea(); }
    public function setProvince($province) { $this->setAdminArea($province); }
    public function getProvince() { return $this->getAdminArea(); }

    /**
     * @return string
     */
    public function getAdminArea()
    {
        return $this->adminArea;
    }

    /**
     * @param string $adminArea
     * @return $this
     */
    public function setAdminArea($adminArea)
    {
        $this->adminArea = $adminArea;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @param string $locality
     * @return $this
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param string $organization
     * @return $this
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortingCode()
    {
        return $this->sortingCode;
    }

    /**
     * @param string $sortingCode
     * @return $this
     */
    public function setSortingCode($sortingCode)
    {
        $this->sortingCode = $sortingCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getStreetLines()
    {
        return $this->streetLines;
    }

    /**
     * @param array $streetLines
     * @return $this
     */
    public function setStreetLines($streetLines)
    {
        $this->streetLines = $streetLines;
        return $this;
    }
}
