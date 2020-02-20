<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;

/**
 * Class InvoiceHandlers
 * @package EzAd\Commerce\Invoicing
 */
class InvoiceHandlers
{
    /**
     * @var array
     */
    private $handlers = [];

    public function __construct()
    {
    }

    public function register($name, InvoiceHandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
    }

    public function findHandler($name)
    {
        return isset($this->handlers[$name]) ? $this->handlers[$name] : null;
    }

    public function getHandlerNames()
    {
        return array_keys($this->handlers);
    }
}
