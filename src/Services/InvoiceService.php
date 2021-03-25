<?php

namespace AroutinR\Invoice\Services;

use AroutinR\Invoice\Interfaces\InvoiceServiceInterface;
use AroutinR\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvoiceService implements InvoiceServiceInterface
{
	public $customer;
	public $invoiceable;
	public $number;
	public $currency;
	public $date;
	public $lines = array();
	public $billingAddress;
	public $shippingAddress;

	public function __construct(Model $customer, Model $invoiceable)
	{
		$this->customer = $customer;
		$this->invoiceable = $invoiceable;

		$this->setCurrency(config('invoice.default_currency'));
		$this->setDate(now()->format('Y-m-d'));
	}

	public function save(): Invoice
	{
		$invoice = Invoice::create([
			'customer_type' => get_class($this->customer),
			'customer_id' => $this->customer->id,
			'invoiceable_type' => get_class($this->invoiceable),
			'invoiceable_id' => $this->invoiceable->id,
			'number' => $this->number,
			'currency' => $this->currency,
			'date' => $this->date
		]);

		$invoice->lines()->createMany($this->lines);

		if ($this->billingAddress) {
			$invoice->billingAddress()->create($this->billingAddress + [
				'customer_type' => get_class($this->customer),
				'customer_id' => $this->customer->id,
			]);
		}

		if ($this->shippingAddress) {
			$invoice->shippingAddress()->create($this->shippingAddress + [
				'customer_type' => get_class($this->customer),
				'customer_id' => $this->customer->id,
			]);
		}

		return $invoice;
	}

	public function setNumber(string $number): InvoiceService
	{
		$this->number = $number;

		return $this;
	}

	public function setCurrency(string $currency): InvoiceService
	{
		if (Str::length($currency) === 3) {
			$this->currency = $currency;

			return $this;
		}

		throw new \Exception('The currency should only be 3 characters long', 1);
	}

	public function setDate(string $date): InvoiceService
	{
		$this->date = $date;

		return $this;
	}

	public function addInvoiceLine(string $description, int $quantity, int $amount): InvoiceService
	{
		$this->lines[] = [
			'line_type' => 'invoice',
			'description' => $description,
			'quantity' => $quantity,
			'amount' => $amount,
		];

		return $this;
	}

	public function addFixedDiscountLine(string $description, int $amount): InvoiceService
	{
		$this->lines[] = [
			'line_type' => 'discount',
			'description' => $description,
			'amount' => $amount,
		];

		return $this;
	}

	public function addPercentDiscountLine(string $description, int $amount): InvoiceService
	{
		$this->lines[] = [
			'line_type' => 'discount',
			'description' => $description,
			'amount' => $amount,
			'percent_based' => true,
		];

		return $this;
	}

	public function addTaxLine(string $description, int $amount): InvoiceService
	{
		$this->lines[] = [
			'line_type' => 'tax',
			'description' => $description,
			'amount' => $amount,
			'percent_based' => true,
		];

		return $this;
	}

	public function addBillingAddress(array $billing): InvoiceService
	{
		$this->billingAddress = [
			'address_type' => 'billing',
			'name' => $billing['name'],
			'line_1' => $billing['line_1'],
			'line_2' => $billing['line_2'],
			'line_3' => $billing['line_3'],
		];

		return $this;
	}

	public function addShippingAddress(array $shipping): InvoiceService
	{
		$this->shippingAddress = [
			'address_type' => 'shipping',
			'name' => $shipping['name'],
			'line_1' => $shipping['line_1'],
			'line_2' => $shipping['line_2'],
			'line_3' => $shipping['line_3'],
		];

		return $this;
	}
}
