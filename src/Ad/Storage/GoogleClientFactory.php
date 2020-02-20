<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad\Storage;

/**
 * This class is responsible for constructing the Google_Client object, with various parameters,
 * access token, etc.
 *
 * @package EzAd\Ad\Storage
 */
class GoogleClientFactory
{
    private $clientId;
    private $appName;
    private $accountName;
    private $keyFile;
    private $keyPassword;
    private $scopes = [];

    /**
     * @param $clientId
     * @param $appName
     * @param $accountName
     * @param $keyFile
     * @param $keyPassword
     * @param $scopes
     */
    public function __construct($clientId, $appName, $accountName, $keyFile, $keyPassword, $scopes)
    {
        $this->clientId = $clientId;
        $this->appName = $appName;
        $this->accountName = $accountName;
        $this->keyFile = $keyFile;
        $this->keyPassword = $keyPassword;
        $this->scopes = $scopes;
    }

    /**
     * @return \Google_Client
     */
    public function create()
    {
        $client = new \Google_Client();
        $client->setApplicationName($this->appName);
        $client->setClientId($this->clientId);

        $key = file_get_contents($this->keyFile);
        $cred = new \Google_Auth_AssertionCredentials($this->accountName, $this->scopes, $key, $this->keyPassword);

        $client->setAssertionCredentials($cred);
        if ( $client->getAuth()->isAccessTokenExpired() ) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        return $client;
    }
}
