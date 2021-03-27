<?php

namespace AroutinR\Invoice\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

    /**
     * Payment constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('invoice.table_names.payments'));
    }

    /**
     * Get the invoice for this payment
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
