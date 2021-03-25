<?php

namespace AroutinR\Invoice\Tests\Models;

use AroutinR\Invoice\Traits\HasInvoice;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
	use HasInvoice;

	/**
	* The attributes that aren't mass assignable.
	*
	* @var array
	*/
	protected $guarded = [];
}