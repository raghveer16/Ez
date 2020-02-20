<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Command;

use EzAd\Bot\ProductStore\ElasticaStore;
use EzAd\EZ;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SearchCommand
 * @package EzAd\Bot\Command
 */
class SearchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('search')
            ->setDescription('Quick search for products by domain')
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name to filter by')
            ->addArgument('query', InputArgument::REQUIRED, 'The search string');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');
        $search = $input->getArgument('query');

        /** @var ElasticaStore $storage */
        $storage = EZ::get('product_store');
        
        if ( preg_match('/^id:(.+)$/', $search, $m) ) {
            $products = [$storage->findById($m[1])];
        } else {
            list($products, $total) = $storage->search($domain, $search, 0, 25);
            $output->writeln("Total: $total");
        }

        foreach ( $products as $product ) {
            $output->writeln("[{$product->getId()}] {$product->getTitle()}");
        }
    }
}
