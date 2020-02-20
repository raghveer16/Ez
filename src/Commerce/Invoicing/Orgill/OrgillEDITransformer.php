<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce\Invoicing\Orgill;
use EzAd\Commerce\Invoicing\EDITransformer;
use EzAd\Commerce\Invoicing\Invoice;
use EzAd\Commerce\Invoicing\LineItem;
use EzAd\Commerce\Money;

/**
 * Orgill-specific EDI transformer.
 *
 * @package EzAd\Commerce\Invoicing\Orgill
 */
class OrgillEDITransformer extends EDITransformer
{
    /**
     * @param Invoice[] $invoices
     * @return string
     */
    public function transform(array $invoices)
    {
        $tsv = $this->getInvoiceHeader();

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

    /**
     * Override to make the address to Orgill instead of the store.
     *
     * @param Invoice $invoice
     * @param Money $credit
     * @return array|void
     */
    protected function getInvoiceColumns(Invoice $invoice, Money $credit)
    {
        $columns = parent::getInvoiceColumns($invoice, $credit);
        array_splice($columns, 14);

        $totalItems = 0;
        foreach ( $invoice->getLineItems() as $item ) {
            $totalItems += $item->getQuantity();
        }

        $columns[3] = 'PP'; // shipment method of payment
        $columns[4] = $totalItems; // units shipped (number of eaches)
        $columns[5] = '0'; // total weight
        $columns[6] = $columns[13]; // ship to number

        /*$columns[7] = 'Orgill Inc.';
        $columns[8] = 'PO Box 140';
        $columns[9] = 'Memphis';
        $columns[10] = 'TN';
        $columns[11] = '38101';*/

        $address = $invoice->getAddress();
        $columns[7] = $address->getName();
        $columns[8] = $address->getStreetText(1);
        $columns[9] = $address->getCity();
        $columns[10] = $address->getAdminArea();
        $columns[11] = $address->getPostalCode();

        $columns[12] = '0';
        $columns[13] = (string) $invoice->getTotal();

        return $columns;
    }

    protected function getLineItemColumns(LineItem $item)
    {
        $columns = parent::getLineItemColumns($item);
        array_splice($columns, 6);

        // 0 = SKU, 1 = blank, already correct
        $columns[2] = $item->getQuantity();
        $columns[3] = 'EA';
        $columns[4] = $item->getPrice();
        $columns[5] = $item->getDescription();

        return $columns;
    }

    protected function getInvoiceColumnCount()
    {
        return 14;
    }

    protected function getInvoiceHeader()
    {
        return "HDR\tORGIL16248\tX\t004010\t810\tP\n\n"
        . "Orgill Invoice Spreadsheet\n\n"
        // invoice-level (14 cols)
        . "Invoice Number\tInvoice Date\tPO #\tShipment Method Of Payment\tUnits Shipped\tTotal Weight\t"
        . "Ship To Number\tShip to Name\tShip to Addr1\tCity\tState\tZip\tFreight\tInvoice Total\t"
        // item-level (6 cols)
        . "Vendor Part Number\tOrgill Part Number\tQuantity Shipped\tUnit of Measure\tUnit Price\tDescription";
    }
}
