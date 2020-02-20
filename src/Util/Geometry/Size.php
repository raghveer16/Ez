<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Util\Geometry;

/**
 * A size has a width and height.
 *
 * @package EzAd\Core\Util\Geometry
 */
class Size
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @param int $width
     * @param int $height
     */
    public function __construct($width = 0, $height = 0)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param string $wxh ex. "1920x1080"
     */
    public static function fromWxH($wxh)
    {
        list($w, $h) = explode('x', $wxh);
        return new Size((int) $w, (int) $h);
    }

    public function __toString()
    {
        return $this->width . 'x' . $this->height;
    }

    /**
     * @return bool True if the width and height are both 0.
     */
    public function isZero()
    {
        return $this->width === 0 && $this->height === 0;
    }

    /**
     * @return int
     */
    public function getArea()
    {
        return $this->width * $this->height;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }
}
