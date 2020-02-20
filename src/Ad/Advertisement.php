<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad;
use EzAd\Ad\DataSource\DataSourceInterface;
use EzAd\Ad\DataSource\FileDataSource;

/**
 * Represents a global advertisement. Can be extended by business ads, company ads, etc.
 *
 * @package EzAd\Ad
 */
class Advertisement
{
    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    /**
     * @var string
     */
    private $name;

    public function setFile($path)
    {
        $this->setDataSource(new FileDataSource($path));
    }

    public function setDataSource(DataSourceInterface $source)
    {
        $this->dataSource = $source;
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
