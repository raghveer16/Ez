<?php

/*
 * EZ-AD Core
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Media\Image;

/**
 * Common operations for images, based on code in library/Image and library/NativeImage.
 *
 * @package EzAd\Core\Image
 */
class ImageTools
{
    private $imagickPath;

    public function __construct($imagickPath)
    {
        $this->imagickPath = $imagickPath;
    }

    private function execCommand($name, $arguments)
    {
        return shell_exec($this->imagickPath . '/' . $name . ' ' . $arguments);
    }

    public function autoOrient($path)
    {
        $path = escapeshellarg($path);
        $this->execCommand('convert', "$path -auto-orient -quality 95 $path");
    }

    public function rotate($path, $deg)
    {
        $path = escapeshellarg($path);
        $deg = (int) $deg;
        $this->execCommand('convert', "$path -rotate $deg -quality 95 $path");
    }

    /**
     * $thumbSpecs = array(
     *   'prefix_' => array('w' => width, 'h' => height, 'q' => quality (0-100))
     * )
     * @param string $path Path to image.
     * @param array $thumbSpecs
     * @throws \RuntimeException
     */
    public function makeMultiThumbs($path, array $thumbSpecs)
    {
        $cmd = array();
        $cmd[] = escapeshellarg($path);
        $cmd[] = '-write mpr:orig';

        $count = count($thumbSpecs);
        $i = 1;

        foreach ( $thumbSpecs as $prefix => $spec ) {
            if ( !isset($spec['w']) || !isset($spec['h']) ) {
                throw new \RuntimeException('thumb spec must have "w" and "h" keys defined');
            }
            if ( !isset($spec['q']) ) {
                $spec['q'] = 75;
            }

            $cmd[] = '+delete';
            $cmd[] = 'mpr:orig -thumbnail';
            $cmd[] = escapeshellarg(intval($spec['w']) . 'x' . intval($spec['h']) . '>');
            $cmd[] = '-quality';
            $cmd[] = (int) $spec['q'];
            if ( $i < $count ) {
                $cmd[] = '-write';
            }

            $name = pathinfo($path, PATHINFO_FILENAME);
            $thumbPath = dirname($path) . '/' . $prefix . $name . '.jpg';
            $cmd[] = escapeshellarg($thumbPath);

            $i++;
        }

        $command = implode(' ', $cmd);
        $this->execCommand('convert', $command);
    }

    public function makeInplaceThumbnail($path, $width, $height)
    {
        $path = escapeshellarg($path);
        $width = (int) $width;
        $height = (int) $height;

        $this->execCommand('convert', "$path -thumbnail {$width}x{$height} -unsharp 0x.5 $path");
    }

    public function removeBackground($path, $fuzz = 2)
    {
        $fileExt = substr($path, strrpos($path, '.'));

        if ( $fileExt != '.png' ) {
            $convertToPNG = true;
        } else {
            $convertToPNG = false;
        }

        $pathEsc = escapeshellarg($path);
        $fuzz = (int) $fuzz;

        if ( $convertToPNG ) {
            $pathPng = str_replace($fileExt, '.png', $path);
            $pathPngEsc = escapeshellarg($pathPng);
            $this->execCommand('convert', "$pathEsc $pathPngEsc");

            $path = $pathPng;
            $pathEsc = $pathPngEsc;
        }

        $command = "$pathEsc -bordercolor white -border 1x1 "
            . "-alpha set -channel RGBA -fuzz $fuzz% -fill none "
            . "-floodfill +0+0 white -shave 1x1 -trim ";

        $command .= "\( +clone -background white -shadow 200x5+0+0 \) "
                . "+swap -background transparent -layers merge +repage ";

        $command .= " $pathEsc";
        $this->execCommand('convert', $command);

        return $path;
    }

    public function getFontWidth($fontPath, $pointSize, $text)
    {
        if ( empty($text) ) {
            return 0;
        }
        $m = $this->getFontMetrics($fontPath, $pointSize, $text);
        return $m['width'];
    }

    // readjusts the point size until we get a size that fits within maxWidth
    public function fitText($fontPath, $pointSize, $text, $maxWidth)
    {
        $pctFactor = 2;
        $sub = round($pointSize * $pctFactor / 100);
        $sub = max(1, $sub);

        do {
            $pointSize -= $sub;
            if ( $pointSize <= 0 ) {
                break;
            }
            $metrics = $this->getFontMetrics($fontPath, $pointSize, $text);
        } while ( $metrics['width'] > $maxWidth );

        return $pointSize;
    }

    public function splitLines($fontPath, $pointSize, $text, $maxWidth)
    {
        $aWidth = $this->getFontWidth($fontPath, $pointSize, 'a');
        $spaceWidth = $this->getFontWidth($fontPath, $pointSize, 'a a') - ($aWidth * 2);

        // split the text into words, add a new line whenever the next word will be > maxwidth
        $newText = '';
        $words = preg_split('/( )+/', $text);
        $ct = count($words);
        $curWidth = 0;
        $widestLine = 0;
        for ( $i = 0; $i < $ct; $i++ ) {
            $word = $words[$i];
            $width = $this->getFontWidth($fontPath, $pointSize, $word);
            $curWidth += $width;
            if ( $curWidth > $maxWidth ) {
                if ( $curWidth - $width > $widestLine ) {
                    $widestLine = $curWidth - $width;
                }
                $newText .= '\n';
                $curWidth = $width;
            }
            $curWidth += $spaceWidth;
            //RPG::debug($curWidth);

            $newText .= $word . ' ';
        }

        return array(rtrim($newText), $widestLine);
    }

    public function makeThumbnail($path, $width, $height)
    {
        $img = new \Imagick($path);
        $img->thumbnailImage($width, $height, true);
        $img->unsharpMaskImage(0, 0.5, 1.0, 0.05);
        $img->writeImage(dirname($path) . '/thumb_' . basename($path));
    }

    public function composite($destImg, $srcImg, $x, $y)
    {
        $dest = new \Imagick($destImg);
        $src  = new \Imagick($srcImg);
        $x = (int) $x;
        $y = (int) $y;

        $dest->compositeImage($src, \Imagick::COMPOSITE_DEFAULT, $x, $y);
        $dest->writeImage($destImg);
    }

    public function getFontMetrics($fontPath, $pointSize, $text)
    {
        $im = new \Imagick();
        $draw = new \ImagickDraw();
        $draw->setFont($fontPath);
        $draw->setFontSize((int) $pointSize);

        $metrics = $im->queryFontMetrics($draw, $text);
        return array(
            'width'  => $metrics['textWidth'],
            'height' => $metrics['textHeight'],
        );
    }
}
