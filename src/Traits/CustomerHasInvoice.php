<?php

namespace AroutinR\Invoice\Traits;

use AroutinR\Invoice\Models\Invoice;

trait CustomerHasInvoice
{
    /**
     * Set the polymorphic relation.
     *
     * @return mixed
     */
    public function invoices()
    {
        return $this->morphMany(Invoice::class, 'customer');
    }
}
