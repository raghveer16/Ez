<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Profile;

/**
 * Manages robot profiles, allows fetching a profile by domain.
 *
 * @package EzAd\Bot\Profile
 */
class ProfileManager
{
    /**
     * Maps profile names to their corresponding profile.
     *
     * @var array
     */
    private $profiles = [];

    /**
     * Maps domain names to profile names.
     *
     * @var array
     */
    private $domainMapping = [];

    /**
     * @param $name
     * @param $className
     * @throws \InvalidArgumentException
     */
    public function registerProfile($name, $className)
    {
        if ( is_object($className) ) {
            if ( $className instanceof ProfileInterface ) {
                $className = get_class($className);
            } else {
                throw new \InvalidArgumentException(
                    'Class name must be a string or an object implementing ProfileInterface');
            }
        }

        $this->profiles[$name] = $className;
    }

    /**
     * @param string $profile
     * @param string|array $domains
     * @throws \InvalidArgumentException
     */
    public function registerDomains($profile, $domains)
    {
        if ( !isset($this->profiles[$profile]) ) {
            throw new \InvalidArgumentException('Profile not found');
        }

        $domains = (array) $domains;
        foreach ( $domains as $d ) {
            $this->domainMapping[$d] = $profile;
        }
    }

    /**
     * Shortcut to register a profile name, its profile class, and the domains that use it.
     *
     * @param $profileName
     * @param $className
     * @param $domains
     */
    public function register($profileName, $className, $domains)
    {
        $this->registerProfile($profileName, $className);
        $this->registerDomains($profileName, $domains);
    }

    /**
     * @param $domain
     * @return ProfileInterface
     */
    public function findForDomain($domain)
    {
        if ( !isset($this->domainMapping[$domain]) ) {
            return null;
        }

        $profileName = $this->domainMapping[$domain];
        if ( !isset($this->profiles[$profileName]) ) {
            return null;
        }

        $className = $this->profiles[$profileName];
        return new $className();
    }
}
