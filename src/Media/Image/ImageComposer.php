<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media\Image;

/**
 * Helper class to compose multiple images.
 *
 * @package EzAd\Core\Image
 */
class ImageComposer
{
    private $path;
    private $image;
    private $isDone = false;

    /**
     * Creates the composer with the first (background) image.
     *
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->image = new \Imagick($path);
    }

    /**
     * Adds a new image on top.
     *
     * @param string|\Imagick $img File path or Imagick object.
     * @param $x
     * @param $y
     * @param int $mode
     * @throws \RuntimeException
     */
    public function add($img, $x, $y, $mode = \Imagick::COMPOSITE_DEFAULT)
    {
        if ( $this->isDone ) {
            throw new \RuntimeException('Cannot add to a composed image marked "done"');
        }

        if ( is_string($img) ) {
            $src = new \Imagick($img);
            $clear = true;
        } else {
            $src = $img;
            $clear = false;
        }
        $x = (int) $x;
        $y = (int) $y;

        $this->image->compositeImage($src, $mode, $x, $y);

        if ( $clear ) {
            $src->clear();
        }
    }

    /**
     * Finishes the composition and writes it to $newPath, or the path given in the constructor.
     *
     * @param null|string $newPath
     */
    public function done($newPath = null)
    {
        $this->image->writeImage($newPath ?: $this->path);
        $this->image->clear();
        $this->isDone = true;
    }
}
