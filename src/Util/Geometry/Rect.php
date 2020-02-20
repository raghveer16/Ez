<?php

namespace EzAd\Util\Geometry;

class Rect
{
    private $point;
    private $size;

    public function __construct($x, $y, $w, $h)
    {
        $this->point = new Point($x, $y);
        $this->size = new Size($w, $h);
    }

    public static function fromArray(array $dims)
    {
        return new Rect($dims[0], $dims[1], $dims[2], $dims[3]);
    }

    public static function fromComponents(Point $point, Size $size)
    {
        return new Rect($point->getX(), $point->getY(), $size->getWidth(), $size->getHeight());
    }

    public static function zero()
    {
        return new Rect(0, 0, 0, 0);
    }

    public function toArray()
    {
        return [$this->getX(), $this->getY(), $this->getWidth(), $this->getHeight()];
    }

    public function getPoint()
    {
        return $this->point;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getX()
    {
        return $this->point->getX();
    }

    public function getY()
    {
        return $this->point->getY();
    }

    public function getWidth()
    {
        return $this->size->getWidth();
    }

    public function getHeight()
    {
        return $this->size->getHeight();
    }

    public function getX2()
    {
        return $this->getX() + $this->getWidth();
    }

    public function getY2()
    {
        return $this->getY() + $this->getHeight();
    }
}
