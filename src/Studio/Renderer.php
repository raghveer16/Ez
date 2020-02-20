<?php

namespace EzAd\Studio;

use EzAd\Util\IHttpClient;
use Psr\Log\LoggerAwareTrait;

/**
 * Transforms a Canvas into a video file. Serialize the Canvas, pass it to the transcoder, then
 * unserialize it and pass it here to get the filter graph?
 */
class Renderer
{
    use LoggerAwareTrait;

    private $tmpDir;
    /** @var IHttpClient */
    private $httpClient;

    public function __construct($tmpDir, IHttpClient $httpClient)
    {
        $this->tmpDir = $tmpDir;
        $this->httpClient = $httpClient;
    }

    /**
     * Download sources in the canvas to the tmpDir and rewrite the URLs to local file paths.
     * @param Canvas $canvas
     */
    private function localizeResources(Canvas $canvas)
    {
        // background image
        if ( $canvas->getBackgroundType() == 'image' ) {
            $bg = $canvas->getBackgroundImage();
            if ( $bg && (strpos($bg, 'http:') === 0 || strpos($bg, 'https:') === 0) ) {
                $ext = substr($bg, strrpos($bg, '.'));
                $localBg = $this->tmpDir . '/canvbg_' . uniqid() . $ext;
                $this->httpClient->get($bg, ['save_to' => $localBg]);

                $canvas->setBackgroundImage($localBg);
            }
        }

        // items
        foreach ( $canvas->getItems() as $item ) {
            $source = $item->getSource();
            if ( strpos($source, 'http:') === 0 || strpos($source, 'https:') === 0 ) {
                $ext = substr($source, strrpos($source, '.'));
                $localFile = $this->tmpDir . '/canvrender_' . uniqid() . $ext;
                $this->httpClient->get($source, ['save_to' => $localFile]);

                $item->setSource($localFile);
            }
        }
    }

    public function cleanupSources(Canvas $canvas)
    {
        foreach ( $this->getSources($canvas) as $source ) {
            if ( is_file($source) && strpos($source, $this->tmpDir) === 0 ) {
                unlink($source);
            }
        }
    }

    public function getSources(Canvas $canvas)
    {
        $sources = [];

        if ( $canvas->getBackgroundType() == 'image' ) {
            $bg = $canvas->getBackgroundImage();
            if ( $bg ) {
                $sources[] = $bg;
            }
        }

        foreach ( $canvas->getItems() as $item ) {
            $src = $item->getSource();
            if ( $src ) {
                $sources[] = $src;
            }
        }

        return $sources;
    }

    public function getFilterGraph(Canvas $canvas)
    {
        /*
        // clone it so we don't rewrite source URLs on the original canvas
        $localCanvas = clone $canvas;

        try {
            $this->localizeResources($localCanvas);
        } catch ( \Exception $e ) {
            if ( $this->logger ) {
                $this->logger->critical('Canvas Renderer unable to localize resources: ' . $e->getMessage());
            }
            return false;
        }

        return $localCanvas->generateFilterGraph()->toString();
        */
    }

    public function getAVFilterGraphs(Canvas $canvas)
    {
        // clone it so we don't rewrite source URLs on the original canvas
        $localCanvas = clone $canvas;

        try {
            $this->localizeResources($localCanvas);
        } catch ( \Exception $e ) {
            if ( $this->logger ) {
                $this->logger->critical('Canvas Renderer unable to localize resources: ' . $e->getMessage());
            }
            return false;
        }

        return [
            'audio' => $localCanvas->generateAudioFilterGraph()->toString(),
            'video' => $localCanvas->generateVideoFilterGraph()->toString(),
        ];
    }
}
