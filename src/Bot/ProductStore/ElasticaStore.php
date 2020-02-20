<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\ProductStore;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;

/**
 * Class ElasticaStore
 * @package EzAd\Bot\ProductStore
 */
class ElasticaStore implements ProductStoreInterface
{
    /**
     * @var \Elastica\Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Product $product
     * @return int|string
     */
    public function saveProduct(Product $product)
    {
        if ( $p = $this->findByExactTitle($product->getDomain(), $product->getTitle()) ) {
            $update = $p->getId();
        } else {
            $update = false;
        }

        $json = [
            'title' => $product->getTitle(),
            'domain' => $product->getDomain(),
            'url' => $product->getUrl(),
            'sku' => $product->getSku(),
            'upc' => $product->getUpc(),
            'date_added' => $product->getDateAdded()->format('Y-m-d H:i:s'),
            'date_modified' => $product->getDateModified()->format('Y-m-d H:i:s'),
            'images' => $product->getImages(),
            'prices' => $product->getPrices(),
            'categories' => $product->getCategories(),
        ];

        // do some change tracking to prevent delete/reindex if nothing is different
        $changed = $update ? false : true;

        if ( $update ) {
            $json['date_added'] = $p->getDateAdded()->format('Y-m-d H:i:s');

            // check if new categories were added
            $newCategories = array_diff($json['categories'], $p->getCategories());
            if ( !empty($newCategories) ) {
                $changed = true;
            }
            $json['categories'] = array_merge($json['categories'], $p->getCategories());

            // check if prices were changed
            if ( count($json['prices']) == count($p->getPrices()) ) {
                $changedPrices = array_udiff($json['prices'], $p->getPrices(), function($a, $b) {
                    $d = $a['amount'] - $b['amount'];
                    if ( $d == 0 ) {
                        $d = strcmp($a['category'], $b['category']);
                    }
                    if ( $d == 0 ) {
                        $d = strcmp($a['currency'], $b['currency']);
                    }
                    return $d;
                });

                if ( !empty($changedPrices) ) {
                    $changed = true;
                }
            } else {
                $changed = true;
            }
        }

        $json['categories'] = array_values(array_unique($json['categories']));

        if ( !$changed ) {
            return $p->getId();
        }

        // just delete + insert, massive in-place updates break things.
        if ( $update ) {
            $this->getType()->deleteIds([$update]);
        }

        $doc = new Document('', $json);
        $this->getType()->addDocument($doc);

        return $doc->getId();
    }
    
    /**
     * @param string $id
     * @return Product|null
     */
    public function findById($id)
    {
        $id = preg_replace('/[^A-Za-z0-9_-]/', '', $id);
        $response = $this->client->request('/ezad_products/product/' . $id, 'GET');
        $response = $response->getData();

        if ( $response['found'] ) {
            return $this->createProduct($response);
        }
        return null;
    }

    /**
     * @param string $domain
     * @param string $title
     * @return Product|null
     */
    public function findByExactTitle($domain, $title)
    {
        $body = [
            'query' => [
                'filtered' => [
                    'filter' => [
                        'term' => [
                            'domain' => $domain,
                            'title.raw' => $title,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request('/ezad_products/product/_search?size=1', 'GET', $body);
        $response = $response->getData();
        $hits = $response['hits'];
        $results = $hits['hits'];

        if ( count($results) > 0 ) {
            return $this->createProduct($results[0]);
        }
        return null;
    }

    /**
     * @param string $domain
     * @param int $category
     * @param int $offset
     * @param int $limit
     * @return Product[]
     */
    public function findByCategory($domain, $category, $offset = 0, $limit = 25)
    {
        $body = [
            'query' => [
                'filtered' => [
                    'filter' => [
                        'term' => [
                            'domain' => $domain,
                            'category' => $category,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request(
            '/ezad_products/product/_search?size=' . $limit . '&from=' . $offset, 'GET', $body);
        $response = $response->getData();
        $hits = $response['hits'];
        $results = $hits['hits'];

        return [$this->createProductList($results), $hits['total']];
    }

    /**
     * @param string $domain
     * @param string $sku
     * @return Product
     */
    public function findBySku($domain, $sku)
    {
        $body = [
            'query' => [
                'filtered' => [
                    'filter' => [
                        'term' => [
                            'domain' => $domain,
                            'sku' => $sku,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request('/ezad_products/product/_search?size=1', 'GET', $body);
        $response = $response->getData();
        $hits = $response['hits'];
        $results = $hits['hits'];

        if ( count($results) > 0 ) {
            return $this->createProduct($results[0]);
        }
        return null;
    }

    /**
     * @param string $domain
     * @param string $upc
     * @return Product
     */
    public function findByUpc($domain, $upc)
    {
        $body = [
            'query' => [
                'filtered' => [
                    'filter' => [
                        'term' => [
                            'domain' => $domain,
                            'upc' => $upc,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request('/ezad_products/product/_search?size=1', 'GET', $body);
        $response = $response->getData();
        $hits = $response['hits'];
        $results = $hits['hits'];

        if ( count($results) > 0 ) {
            return $this->createProduct($results[0]);
        }
        return null;
    }

    /**
     * Search across title, SKU, and UPC. Should try to match SKU and UPC, and if so the score is boosted.
     *
     * @param string $domain
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @return Product[]
     */
    public function search($domain, $search, $offset = 0, $limit = 25)
    {
        // search by title, and boost a lot if SKU and/or UPC match the search.
//        $body = [
//            'query' => [
//                'function_score' => [
//                    'functions' => [
//                        [
//                            'boost_factor' => 3.0,
//                            'filter' => [
//                                'term' => ['sku' => $search]
//                            ]
//                        ],
//                        [
//                            'boost_factor' => 3.0,
//                            'filter' => [
//                                'term' => ['upc' => $search]
//                            ]
//                        ]
//                    ],
//                    'query' => [
//                        'match' => [
//                            'title' => $search,
//                        ]
//                    ],
//                    'filter' => [
//                        'term' => [
//                            'domain' => $domain,
//                        ]
//                    ]
//                ]
//            ]
//        ];

        $body = [
            'query' => [
                'filtered' => [
                    'query' => [
                        'match' => [
                            'title' => $search,
                        ]
                    ],
                    'filter' => [
                        'term' => [
                            'domain' => $domain,
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->request(
            '/ezad_products/product/_search?size=' . $limit . '&from=' . $offset, 'GET', $body);
        $response = $response->getData();
        $hits = $response['hits'];
        $results = $hits['hits'];
        $total = $hits['total'];

        $searchResults = $this->createProductList($results);

        // change this to an OR filter to reduce an api call
        $bySku = $this->findBySku($domain, $search);
        $byUpc = $this->findByUpc($domain, $search);
        if ( $byUpc ) {
            array_unshift($searchResults, $byUpc);
            $total++;
        }
        if ( $bySku ) {
            array_unshift($searchResults, $bySku);
            $total++;
        }

        return [$searchResults, $total];
    }

    private function getType()
    {
        return $this->client->getIndex('ezad_products')->getType('product');
    }

    private function createProductList($results)
    {
        return array_map(function($result) {
            return $this->createProduct($result);
        }, $results);
    }

    private function createProduct($result)
    {
        $src = $result['_source'];

        $product = new Product();
        $product->setId($result['_id']);
        $product->setTitle($src['title']);
        $product->setCategories($src['categories']);
        $product->setDateAdded(date_create($src['date_added']));
        $product->setDateModified(date_create($src['date_modified']));
        $product->setDomain($src['domain']);
        $product->setImages($src['images']);
        $product->setPrices($src['prices']);
        $product->setSku($src['sku']);
        $product->setUpc($src['upc']);
        $product->setUrl($src['url']);

        return $product;
    }
}

/*
PUT /ezad_products
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

// example record. when done, image URL will be controlled by us.
{
   "categories": [1, 3, 5],
   "date_added": "2014-07-18 19:14:34",
   "date_modified": "2014-07-18 19:14:34",
   "domain": "mygrandrental.com",
   "images": [
      "http://assets.newmediaretailer.com/145000/145923/web_350dd-eproxy_new.png"
   ],
   "prices": [
      {
         "amount": 2000,
         "category": "Half Day",
         "currency": "USD"
      },
      {
         "amount": 3200,
         "category": "Day",
         "currency": "USD"
      },
      {
         "amount": 9600,
         "category": "Week",
         "currency": "USD"
      },
      {
         "amount": 38400,
         "category": "Month",
         "currency": "USD"
      }
   ],
   "sku": "",
   "upc": "",
   "title": "Kushlan 350DD Concrete Mixer",
   "url": "http://mygrandrental.com/catalog/product/99401/kushlan-350dd-concrete-mixer"
}
 */
