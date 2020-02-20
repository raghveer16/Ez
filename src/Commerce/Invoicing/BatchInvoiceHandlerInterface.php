<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;

/**
 * Interface BatchInvoiceHandlerInterface
 * @package EzAd\Commerce\Invoicing
 */
interface BatchInvoiceHandlerInterface extends InvoiceHandlerInterface
{
    /**
     * Do anything related to initializing a batch send.
     */
    public function initBatch();

    /**
     * Finalize the sending process.
     */
    public function finalizeBatch();
}
