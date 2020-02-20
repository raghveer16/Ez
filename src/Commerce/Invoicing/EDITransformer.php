<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing;
use EzAd\Commerce\Money;

/**
 * Transforms an invoice into a tab-separated value format that can be uploaded to EDI.
 *
 * @package EzAd\Commerce\Invoicing
 */
class EDITransformer
{
    /**
     * @param Invoice[] $invoices
     * @return string
     */
    public function transform(array $invoices)
    {
        $tsv = $this->getInvoiceHeader();

        // encode like this:
        // (start)
        // invoice	header	fields	here	item	fields	here
        //									item	fields	here
        // another	invoice	goes	here	blah	blah	blah
        // (end)

        foreach ( $invoices as $invoice ) {
            $credit = new Money('0');
            foreach ( $invoice->getLineItems() as $lineItem ) {
                if ( $lineItem->getPrice()->compare('0') < 0 ) {
                    $credit = $credit->add($lineItem->getPrice());
                }
            }

            $invenc = $this->encodeInvoice($invoice, $credit);
            $tsv .= "\n" . $invenc;

            $firstitem = true;
            foreach ( $invoice->getLineItems() as $item ) {
                // don't encode items with a negative value, they are added as credit to the invoice
                if ( $item->getPrice()->compare('0') < 0 ) {
                    continue;
                }

                if ( !$firstitem ) {
                    $tsv .= "\n";
                    $tsv .= str_repeat("\t", $this->getInvoiceColumnCount());
                } else {
                    $tsv .= "\t";
                }
                $tsv .= $this->encodeLineItem($item);
                $firstitem = false;
            }
        }

        return $tsv;
    }

    protected function getInvoiceColumns(Invoice $invoice, Money $credit)
    {
        $invdate = $invoice->getInvoiceDate()->format('m/d/Y');
        $address = $invoice->getAddress();
        $hasCredit = !$credit->isZero();

        return [
            $invoice->getInvoiceNumber(),
            $invdate,
            $invoice->getPoNumber() ?: $invoice->getInvoiceNumber(),
            $invdate,
            $invdate,
            '',
            '',
            '',
            $hasCredit ? 'F670' : '',
            $hasCredit ? $credit->getValue() : '',
            $hasCredit ? 'DISCOUNT' : '',
            (string) $invoice->getTotal(),
            '',
            str_pad(str_replace('-', '', $invoice->getStoreNumber()), 6, '0', STR_PAD_LEFT),
            $address->getName(),
            $address->getStreetText(1),
            $address->getCity(),
            $address->getAdminArea(),
            $address->getPostalCode(),
            '',
            '',
            $invoice->getDueDate()->format('m/d/Y'),
            '',
            '',
        ];
    }

    public function encodeInvoice(Invoice $invoice, Money $credit)
    {
        $columns = $this->getInvoiceColumns($invoice, $credit);
        return implode("\t", $columns);
    }

    protected function getLineItemColumns(LineItem $item)
    {
        return [
            $item->getSku(),
            '',
            '',
            '',
            $item->getQuantity(),
            'EA',
            (string) $item->getPrice(),
            preg_replace('/[^A-Za-z0-9 ]/', '', strip_tags($item->getDescription())),
        ];
    }

    protected function getInvoiceColumnCount()
    {
        return 24;
    }

    public function encodeLineItem(LineItem $item)
    {
        $columns = $this->getLineItemColumns($item);
        return implode("\t", $columns);
    }

    protected function getInvoiceHeader()
    {
        return "HDR	COTT 16248	X	004010	810	P\n\n"
            . "TRUE VALUE FTP INVOICE SPREADSHEET\n"
            . "                                                      INVOICE HEADER LEVEL" . str_repeat("\t", 24) . "ITEM LEVEL\n"
            . "Invoice Number\tInvoice Date MM/DD/YYYY format\tPO Number\tPO Date MM/DD/YYYY format\t"
            . "Ship Date MM/DD/YYYY format\tSCAC\tCarrier Name\tFREIGHT CHARGES\tOther Allowance/ Charge Type\t"
            . "Other A/C Amount\tOther A/C Description\tInvoice Total\tShip to RDC #\tDrop Ship Store #\tShip to Name\t"
            . "Ship to Address1\tShip to City\tShip to State\tShip to Zip\tDiscount Percent\t"
            . "Discount Due Date MM/DD/YYYY format\tNet Due Date MM/DD/YYYY format\tAuth #\tPromo #\tVendor Item Number\t"
            . "True Value Item Number\tUPC Number\tGTIN Number\tQuantity\tUOM\tUnit Price\tItem Description";
    }
}
