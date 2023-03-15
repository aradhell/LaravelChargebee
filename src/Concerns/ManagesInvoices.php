<?php

namespace TijmenWierenga\LaravelChargebee\Concerns;

use ChargeBee\ChargeBee\Models\Invoice;

trait ManagesInvoices
{
    /**
     * @param array $options
     * @return Invoice
     */
    public function createForChargeItemsAndCharges(array $options): Invoice
    {
        $result = Invoice::createForChargeItemsAndCharges($options);
        return $result->invoice();
    }
}
