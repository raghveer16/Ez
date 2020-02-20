<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;

use EzAd\EZ;
use EzAd\Util\FTP;

/**
 * Handler that uploads invoices to EDI.
 *
 * @package EzAd\Commerce\Invoicing
 */
class EDIHandler implements BatchInvoiceHandlerInterface
{
    const FTP_HOST = 'ftp.apiec.com';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Prefix for uploaded files, like "IN016248" in files named IN016248.001, IN016248.002, etc.
     *
     * @var string
     */
    private $filePrefix;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var FTP
     */
    private $connection;

    /**
     * @var EDITransformer
     */
    private $transformer;

    /**
     * @param string $username
     * @param string $password
     * @param string $filePrefix
     * @param string $host
     * @param int $port
     */
    public function __construct($username, $password, $filePrefix, $host = self::FTP_HOST, $port = 21)
    {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;

        $this->filePrefix = $filePrefix;
        $this->transformer = new EDITransformer();
    }

    /**
     * @param EDITransformer $transformer
     */
    public function setTransformer(EDITransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * Do anything related to initializing a batch send.
     */
    public function initBatch()
    {
        $this->connect();
    }

    /**
     * Finalize the sending process.
     */
    public function finalizeBatch()
    {
        if ( $this->connection ) {
            //ftp_close($this->connection);
        }
    }

    /**
     * Handle a single invoice.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function handleInvoice(Invoice $invoice)
    {
        $tsv = $this->transformer->transform([$invoice]);
        return $this->uploadTSVData($tsv);
    }

    /**
     * @return bool
     */
    public function connect()
    {
        $this->connection = new FTP($this->host, $this->username, $this->password, $this->port);
        return $this->connection->connect();
    }

    /**
     * @param $data
     * @return bool
     */
    public function uploadTSVData($data)
    {
        $filename = $this->filePrefix . '.' . $this->getNextInputId();
        return $this->putString($data, $filename);
    }

    /**
     * @return string
     */
    private function getNextInputId()
    {
        $files = $this->connection->nlist();
        $prefix = substr($this->filePrefix, 0, 2);
        $files = array_filter($files, function($f) use ($prefix) {
            return substr($f, 0, 2) == $prefix;
        });

        if ( count($files) == 0 ) {
            return '001';
        }

        sort($files, SORT_STRING);
        $latest = array_pop($files);
        $id = substr($latest, strrpos($latest, '.') + 1);

        if ( $id == '999' ) {
            return '001'; // ??
        }

        $id = (int) $id;
        return str_pad(strval($id + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * @param $data
     * @param $remoteFile
     * @param int $mode
     * @return bool
     */
    private function putString($data, $remoteFile, $mode = FTP_ASCII)
    {
        return $this->connection->putString($remoteFile, $data);
    }

    /**
     * @return string
     */
    private function makeTmpPath()
    {
        $tmp = EZ::get('tmp_path') . '/edi';
        if ( !is_dir($tmp) ) {
            mkdir($tmp, 0777, true);
        }
        return $tmp;
    }
}
