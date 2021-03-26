<?php

namespace AroutinR\Invoice\Services;

use AroutinR\Invoice\Interfaces\InvoiceServiceInterface;
use AroutinR\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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
	public $customFields = array();

	public function __construct(Model $customer, Model $invoiceable)
	{
		$this->customer = $customer;
		$this->invoiceable = $invoiceable;

		$this->setCurrency(config('invoice.currency'));
		$this->setDate(now()->format('Y-m-d'));
	}

	public function save(): Invoice
	{
		if (!in_array('invoice', Arr::flatten($this->lines))) {
			throw new \Exception('You must add at least one invoice line to the invoice', 1);
		}

		$invoice = Invoice::create([
			'customer_type' => get_class($this->customer),
			'customer_id' => $this->customer->id,
			'invoiceable_type' => get_class($this->invoiceable),
			'invoiceable_id' => $this->invoiceable->id,
			'number' => $this->number,
			'currency' => $this->currency,
			'date' => $this->date,
			'amount' => $this->calculateInvoiceAmount(),
			'custom_fields' => $this->customFields
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
		if (Arr::exists($this->lines, 'discount')) {
			throw new \Exception('Only one discount line can be added', 1);
		}

		$this->lines['discount'] = [
			'line_type' => 'discount',
			'description' => $description,
			'amount' => $amount,
			'percent_based' => false,
		];

		return $this;
	}

	public function addPercentDiscountLine(string $description, int $amount): InvoiceService
	{
		if (Arr::exists($this->lines, 'discount')) {
			throw new \Exception('Only one discount line can be added', 1);
		}

		$this->lines['discount'] = [
			'line_type' => 'discount',
			'description' => $description,
			'amount' => $amount,
			'percent_based' => true,
		];

		return $this;
	}

	public function addTaxLine(string $description, int $amount): InvoiceService
	{
		if (Arr::exists($this->lines, 'tax')) {
			throw new \Exception('Only one tax line can be added', 1);
		}

		$this->lines['tax'] = [
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

	public function addCustomField(string $name, string $value): InvoiceService
	{
		if (count($this->customFields) === config('invoice.custom_fields', 4)) {
			throw new \Exception('You can add a maximum of ' . config('invoice.custom_fields', 4) .' custom fields', 1);
		}

		$this->customFields[] = [
			'name' => $name,
			'value' => $value,
		];

		return $this;
	}

	protected function calculateInvoiceAmount(): int
	{
		$amount = 0;

		foreach ($this->lines as $line) {
			if ($line['line_type'] === 'invoice') {
				$amount += $line['quantity'] * $line['amount'];
			}
		}

		if (Arr::exists($this->lines, 'discount')) {
			$amount -= $this->lines['discount']['percent_based'] 
				? $amount * $this->lines['discount']['amount'] / 100
				: $this->lines['discount']['amount'];
		}

		if (Arr::exists($this->lines, 'tax')) {
			$amount += $amount * ($this->lines['tax']['amount'] / 100);
		}

		return $amount;
	}
}
