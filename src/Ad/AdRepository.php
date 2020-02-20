<?php

/**
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Ad;
use Doctrine\DBAL\Connection;

/**
 * Class AdRepository
 * @package EzAd\Ad
 *
 * $app['ad_repository'] = function($app) {
 *   return new \EzAd\Ad\AdRepository($app['database']);
 * };
 */
class AdRepository
{
    const IMAGE = 1;
    const TEMPLATE = 2;
    const VIDEO = 3;
    const YOUTUBE = 4;
    const YOUTUBE_EXT = 5; // external youtube, will download and convert to normal YOUTUBE when used
    const VIDEO_TEMPLATE = 6;
    const FACEBOOK_POST = 7;

    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param array $ad
     * @return array
     */
    public function create($ad)
    {
        $ad['date_added'] = date('Y-m-d H:i:s');
        $ad['reference_count'] = 1;

        $this->db->insert('ad_global', $ad);
        $ad['id'] = $this->db->lastInsertId();

        return $ad;
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $defaults
     * @return array
     */
    public function createImage($name, $path, $defaults = [])
    {
        $ad = $defaults;
        $ad['source'] = AdSource::file($path);
        $ad['name'] = $name;
        $ad['type'] = self::IMAGE;
        $ad['is_ready'] = 1;
        $ad['file_size'] = filesize($path);
        return $this->create($ad);
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $defaults
     * @return array
     */
    public function createTemplate($name, $path, $defaults = [])
    {
        $ad = $defaults;
        $ad['source'] = AdSource::file($path);
        $ad['name'] = $name;
        $ad['type'] = self::TEMPLATE;
        $ad['is_ready'] = 1;
        $ad['file_size'] = filesize($path);

        return $this->create($ad);
    }

    /**
     * @param string $name
     * @param string $path
     * @param array $defaults
     * @return array
     */
    public function createVideo($name, $path, $defaults = [])
    {
        $ad = $defaults;
        $ad['source'] = AdSource::file($path);
        $ad['name'] = $name;
        $ad['type'] = self::VIDEO;
        $ad['is_ready'] = (isset($defaults['is_ready']) && $defaults['is_ready']) ? 1 : 0;
        $ad['file_size'] = filesize($path);
        return $this->create($ad);
    }

    /**
     * @param string $name
     * @param string $videoId
     * @param null|int $startSec
     * @param null|int $endSec
     * @param bool $mute
     * @param array $defaults
     * @return array
     */
    public function createYoutube($name, $videoId, $startSec = null, $endSec = null, $mute = false, $defaults = [])
    {
        $ad = $defaults;
        $ad['source'] = AdSource::youtube($videoId, $startSec, $endSec, $mute);
        $ad['name'] = $name;
        $ad['type'] = self::YOUTUBE;
        $ad['is_ready'] = (isset($defaults['is_ready']) && $defaults['is_ready']) ? 1 : 0;
        $ad['file_size'] = isset($defaults['file_size']) ? $defaults['file_size'] : 0;

        if ( $startSec !== null && $endSec !== null ) {
            $ad['video_duration'] = $endSec - $startSec + 1;
        }

        return $this->create($ad);
    }

    /**
     * @param string $name
     * @param string $videoId
     * @param int $fileSize
     * @param int $duration
     * @return array
     */
    public function createYoutubeExt($name, $videoId, $fileSize, $duration)
    {
        $ad = [
            'source' => AdSource::youtube($videoId, null, null, false),
            'name' => $name,
            'type' => self::YOUTUBE_EXT,
            'is_ready' => 1,
            'file_size' => $fileSize,
            'video_duration' => $duration,
        ];
        return $this->create($ad);
    }

    public function createVideoTemplate($name, $jsonData, $defaults = array())
    {
        $data = json_decode($jsonData);

        $ad = $defaults;
        $ad['source'] = AdSource::videoCanvas($jsonData);
        $ad['name'] = $name;
        $ad['type'] = self::VIDEO_TEMPLATE;
        $ad['is_ready'] = (isset($defaults['is_ready']) && $defaults['is_ready']) ? 1 : 0;
        $ad['video_duration'] = $data->duration;
        $ad['file_size'] = $data->duration * 200000; // 1600kbit/sec estimate?
        return $this->create($ad);
    }

    /**
     * @param int $id
     * @return array
     */
    public function find($id)
    {
        return $this->db->fetchAssoc('SELECT * FROM ad_global WHERE id = ?', array($id));
    }

    /**
     * @param string $source
     * @return array
     */
    public function findBySource($source)
    {
        return $this->db->fetchAssoc('SELECT * FROM ad_global WHERE source = ?', array($source));
    }

    /**
     * @param array $ad
     */
    public function update(array $ad)
    {
        $id = $ad['id'];
        unset($ad['id']);
        $this->db->update('ad_global', $ad, ['id' => $id]);
    }

    /**
     * @param array $ad
     * @param array $files
     */
    public function attachFiles(array $ad, array $files)
    {
        foreach ( $files as $file ) {
            if ( !isset($file['type']) || !isset($file['url']) ) {
                continue;
            }
            $this->db->insert('ad_attachments', [
                'ad_id' => $ad['id'],
                'type' => $file['type'],
                'file_url' => $file['url'],
            ]);
        }
    }

    /**
     * @param int $globalId
     * @return array
     */
    public function getAttachments($globalId)
    {
        if ( is_array($globalId) ) {
            $globalId = isset($globalId['id']) ? $globalId['id'] : $globalId['global_id'];
        }

        $rs = $this->db->executeQuery('SELECT type, file_url FROM ad_attachments WHERE ad_id = ?', array($globalId));
        $attachments = [];
        while ( $row = $rs->fetch() ) {
            $attachments[ $row['type'] ] = $row['file_url'];
        }

        return $attachments;
    }

    /**
     * @param array $ad
     */
    public function increment(array &$ad)
    {
        $ad['reference_count']++;
        $this->db->update('ad_global', ['reference_count' => $ad['reference_count']], ['id' => $ad['id']]);
    }

    /**
     * @param array $ad
     */
    public function decrement(array &$ad)
    {
        $ad['reference_count']--;
        $this->db->update('ad_global', ['reference_count' => $ad['reference_count']], ['id' => $ad['id']]);
    }
}
