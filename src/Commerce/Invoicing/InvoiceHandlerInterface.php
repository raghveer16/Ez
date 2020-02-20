<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;

/**
 * Interface InvoiceHandlerInterface
 * @package EzAd\Commerce\Invoicing
 */
interface InvoiceHandlerInterface
{
    /**
     * Handle a single invoice.
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function handleInvoice(Invoice $invoice);
}
