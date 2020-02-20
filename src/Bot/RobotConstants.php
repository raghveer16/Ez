<?php

/*
 * EZ-AD Bot
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Bot;

/**
 * Class RobotConstants
 * @package EzAd\Bot
 */
abstract class RobotConstants
{
    /**
     * User-agent for the bot.
     */
    const USER_AGENT = 'Mozilla/5.0 (compatible; ezad-bot/0.1; +https://ezadtv.com/)';

    /**
     * Short identifier for the user-agent.
     */
    const USER_AGENT_SHORT = 'ezad-bot';

    /**
     * Max random delay in addition to crawl delay.
     */
    const EXTRA_DELAY_MAX_MS = 1000;
}
