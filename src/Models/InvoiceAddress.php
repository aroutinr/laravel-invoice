<?php

namespace AroutinR\Invoice\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAddress extends Model
{
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

    /**
     * InvoiceLine constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.invoice_addresses'));
    }

    /**
     * Get the invoice for this line
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
