<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot\Command;

use EzAd\Bot\Profile\ProfileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StartCommand
 * @package EzAd\Bot\Command
 */
class StartCommand extends Command
{
    protected function configure()
    {
        $this->setName('start')
            ->setDescription('Starts a robot by a domain name')
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain to crawl')
            ->addOption('norestore', null, InputOption::VALUE_NONE, 'Do not restore state');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getArgument('domain');
        $output->writeln("Starting bot for $domain");

        $profileManager = new ProfileManager();
        ezad_bot_init_profiles($profileManager);

        $profile = $profileManager->findForDomain($domain);
        $robot = $profile->createNewRobot($domain);

        if ( !$input->getOption('norestore') ) {
            $restored = $profile->maybeRestore($robot);
            if ( $restored ) {
                $output->writeln('Restoring robot state');
            }
        }

        $robot->start();
    }
}
