<?php

namespace EzAd\Media\Subtitle;

/**
 * Represents a single subtitle, with a time span and styled text.
 *
 * Good reference - http://docs.aegisub.org/3.2/ASS_Tags/
 */
class Entry
{
    const WRAP_SMART_TOP = 0;
    const WRAP_EOL = 1;
    const WRAP_NONE = 2;
    const WRAP_SMART_BOTTOM = 3;

    /** @var float */
    public $startSeconds;
    /** @var float */
    public $endSeconds;
    /** @var string */
    public $text;

    public $italic = false;
    public $bold = false;
    public $underline = false;
    public $strikeout = false;

    // remember for colors, when writing to .ass file, order is BBGGRR.
    // eg. red fill with blue stroke is {\c&H0000FF&\3c&HFF0000&}
    public $fillColor = 0xffffff;

    public $strokeWidth = 0;
    public $strokeColor = 0x000000;

    public $fontFamily = 'Arial';
    /** @var int */
    public $fontSize = 36;

    /** @var int */
    public $positionX = 0;
    /** @var int */
    public $positionY = 0;

    public $dimensions = null;

    /**
     * Note that if this is set, will need to calculate the center of the text and set \org(x,y) so
     * it rotates around the center point and not the top-left corner.
     *
     * @var int
     */
    public $rotation = 0;

    public $wrapStyle = self::WRAP_NONE;

    public function __construct($start, $end, $posX, $posY, $text)
    {
        $this->startSeconds = (float) $start;
        $this->endSeconds = (float) $end;

        $this->positionX = (int) $posX;
        $this->positionY = (int) $posY;

        $this->text = $text;
    }

    public function getDimensions()
    {
        if ( $this->dimensions === null ) {
            $im = new \Imagick();
            $draw = new \ImagickDraw();
            $draw->setFontFamily($this->fontFamily);
            $draw->setFontSize((int) $this->fontSize);

            $metrics = $im->queryFontMetrics($draw, $this->text);
            $this->dimensions = [$metrics['textWidth'], $metrics['textHeight']];
        }

        return $this->dimensions;
    }

    public function getAssOverrideTags()
    {
        // position
        $tags = '\pos(' . $this->positionX . ',' . $this->positionY . ')';

        // rotation
        if ( $this->rotation != 0 ) {
            list($width, $height) = $this->getDimensions();
            $cx = round($this->positionX + $width / 2);
            $cy = round($this->positionY + $height / 2);

            $tags .= '\org(' . $cx . ',' . $cy . ')';
            $tags .= '\frz' . $this->rotation;
        }

        // basic styles
        if ( $this->italic ) {
            $tags .= '\i1';
        }
        if ( $this->bold ) {
            $tags .= '\b1';
        }
        if ( $this->underline ) {
            $tags .= '\u1';
        }
        if ( $this->strikeout ) {
            $tags .= '\s1';
        }

        // stroke
        if ( $this->strokeWidth > 0 ) {
            $tags .= '\bord' . $this->strokeWidth;
            $tags .= '\3c&H' . $this->getBGRHex($this->strokeColor) . '&';
        }

        // fill
        $tags .= '\c&H' . $this->getBGRHex($this->fillColor) . '&';

        // font
        $tags .= '\fn' . $this->fontFamily;
        $tags .= '\fs' . $this->fontSize;

        // wrap
        if ( $this->wrapStyle != self::WRAP_NONE ) {
            $tags .= '\q' . $this->wrapStyle;
        }

        return $tags;
    }

    private function getBGRHex($intValue)
    {
        $r = ($intValue >> 16) & 0xff;
        $g = ($intValue >> 8) & 0xff;
        $b = $intValue & 0xff;

        $bgr = ($b << 16) | ($g << 8) | $r;
        return strtoupper(sprintf('%06s', dechex($bgr)));
    }
}
