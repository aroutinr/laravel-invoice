<?php

namespace AroutinR\Invoice\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'custom_fields' => 'array'
    ];

    /**
     * Invoice constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.invoices'));
    }

    /**
     * Get the invoice customer model
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function customer()
    {
        return $this->morphTo();
    }

    /**
     * Get the invoice invoiceable model
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function invoiceable()
    {
        return $this->morphTo();
    }

    /**
     * Get the invoice lines for this invoice
     */
    public function lines()
    {
        return $this->hasMany(InvoiceLine::class)
            ->where('line_type', 'invoice');
    }

    /**
     * Get the discount line for this invoice
     */
    public function discount()
    {
        return $this->hasOne(InvoiceLine::class)
            ->where('line_type', 'discount');
    }

    /**
     * Get the tax line for this invoice
     */
    public function tax()
    {
        return $this->hasOne(InvoiceLine::class)
            ->where('line_type', 'tax');
    }

    /**
     * Get the addresses for this invoice
     */
    public function addresses()
    {
        return $this->hasMany(InvoiceAddress::class);
    }

    /**
     * Get the billing address for this invoice
     */
    public function billingAddress()
    {
        return $this->hasOne(InvoiceAddress::class)
            ->where('address_type', 'billing');
    }

    /**
     * Get the shipping address for this invoice
     */
    public function shippingAddress()
    {
        return $this->hasOne(InvoiceAddress::class)
            ->where('address_type', 'shipping');
    }

    /**
     * Get the invoice amount without discount and tax
     */
    public function getLinesAmountAttribute()
    {
        $amount = 0;

        foreach ($this->lines as $line) {
            $amount += $line->quantity * $line->amount;
        }

        return $amount;
    }
}
