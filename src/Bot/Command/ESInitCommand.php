<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Command;
use Elastica\Client;
use Elastica\Request;
use EzAd\EZ;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initializes the ElasticSearch index for products.
 *
 * @package EzAd\Bot\Command
 */
class ESInitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('es:init')
            ->setDescription('Initializes the product index for ElasticSearch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Client $client */
        $client = EZ::get('elastica_client');

        $type = $client->getIndex('ezad_products')->getType('product');
        if ( $type->exists() ) {
            $output->writeln('ezad_products/product type already exists');
            exit;
        }

        $newIndexJson = <<<JSON
{
  "mappings": {
    "product": {
      "properties": {
        "title": {
          "type" : "string",
          "index" : "analyzed",
          "fields" : {
            "raw" : {"type" : "string", "index" : "not_analyzed"}
          }
        },
        "domain": {"type": "string", "index": "not_analyzed"},
        "url": {"type": "string", "index": "not_analyzed"},
        "sku": {"type": "string", "index": "not_analyzed"},
        "upc": {"type": "string", "index": "not_analyzed"},
        "images": {
          "type": "string",
          "index": "not_analyzed",
          "index_name": "image"
        },
        "prices": {
          "type": "nested",
          "index_name": "price",
          "properties": {
            "amount": {"type": "long"},
            "currency": {"type": "string", "index": "not_analyzed"},
            "category": {"type": "string", "index": "not_analyzed"}
          }
        },
        "categories": {
          "type": "long",
          "index_name": "category"
        },
        "date_added": {
          "type": "date",
          "format": "yyyy-MM-dd HH:mm:ss"
        },
        "date_modified": {
          "type": "date",
          "format": "yyyy-MM-dd HH:mm:ss"
        }
      }
    }
  }
}
JSON;

        $response = $client->request('ezad_products', Request::PUT, $newIndexJson);
        $status = $response->getStatus();
        $data = $response->getData();

        if ( $status >= 200 && $status < 300 ) {
            $output->writeln('Status: OK - ' . $status);
        } else {
            $output->writeln('Status: ERR - ' . $status);
            $output->write(print_r($data, true));
        }
    }
}
