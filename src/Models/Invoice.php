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
     * Get the invoice balance
     */
    public function getBalanceAttribute()
    {
        return $this->amount - $this->payments()->sum('amount');
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

    /**
     * Get the invoice amount only with discount
     */
    public function getLinesAmountWithDiscountAttribute()
    {
        $amount = $this->getLinesAmountAttribute();

        if ($this->discount) {
            $amount -= $this->discount->percent_based
                ? $amount * $this->discount->amount / 100
                : $this->discount->amount;
        }

        return $amount;
    }

    /**
     * Get the invoice discount amount
     */
    public function getDiscountAmountAttribute()
    {
        if (!$this->discount) {
            throw new \Exception("This invoice does not have discounts", 1);
        }

        return $this->discount->percent_based
            ? $this->getLinesAmountAttribute() * $this->discount->amount / 100
            : $this->discount->amount;
    }

    /**
     * Get the invoice tax amount
     */
    public function getTaxAmountAttribute()
    {
        if (!$this->tax) {
            throw new \Exception("This invoice does not have taxes", 1);
        }

        return $this->getLinesAmountWithDiscountAttribute() * ($this->tax->amount / 100);
    }
}
