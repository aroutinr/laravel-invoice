<?php

namespace AroutinR\Invoice\Traits;

use AroutinR\Invoice\Models\InvoiceAddress;

trait HasAddresses
{
    /**
     * Get the addresses for this customer
     */
    public function addresses()
    {
        return $this->hasMany(InvoiceAddress::class, 'customer_id');
    }

    /**
     * Get the billing addres for this customer
     */
    public function billingAddress()
    {
        return $this->hasOne(InvoiceAddress::class, 'customer_id')
            ->where('address_type', 'billing');
    }

    /**
     * Get the shipping addres for this customer
     */
    public function shippingAddress()
    {
        return $this->hasOne(InvoiceAddress::class, 'customer_id')
            ->where('address_type', 'shipping');
    }
}
