<?php

namespace AroutinR\Invoice\Services;

use AroutinR\Invoice\Interfaces\InvoiceServiceInterface;
use AroutinR\Invoice\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
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
	public $note;

	public function __construct()
	{
		$this->invoiceCurrency(config('invoice.currency'));
		$this->invoiceDate(now()->format('Y-m-d'));
	}

	public function for(Model $customer, Model $invoiceable): InvoiceService
	{
		$this->customer = $customer;
		$this->invoiceable = $invoiceable;

		return $this;
	}

	public function save(): Invoice
	{
		if (!$this->customer || !$this->invoiceable) {
			throw new \Exception('You must add a Customer and Invoiceable model', 1);
		}

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
			'custom_fields' => $this->customFields,
			'note' => $this->note,
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

		$this->resetInvoiceService();

		return $invoice;
	}

	public function invoiceNumber(string $number): InvoiceService
	{
		$this->number = $number;

		return $this;
	}

	public function invoiceCurrency(string $currency): InvoiceService
	{
		if (Str::length($currency) === 3) {
			$this->currency = $currency;

			return $this;
		}

		throw new \Exception('The currency should only be 3 characters long', 1);
	}

	public function invoiceDate(string $date): InvoiceService
	{
		$this->date = $date;

		return $this;
	}

	public function invoiceLine(string $description, int $quantity, int $amount): InvoiceService
	{
		$this->lines[] = [
			'line_type' => 'invoice',
			'description' => $description,
			'quantity' => $quantity,
			'amount' => $amount,
		];

		return $this;
	}

	public function invoiceLines(array $invoice_lines): InvoiceService
	{
		foreach ($invoice_lines as $line) {
			$this->invoiceLine(
				$line['description'], 
				$line['quantity'], 
				$line['amount']
			);
		}

		return $this;
	}

	public function fixedDiscountLine(string $description, int $amount): InvoiceService
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

	public function percentDiscountLine(string $description, int $amount): InvoiceService
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

	public function taxLine(string $description, int $amount): InvoiceService
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

	public function billingAddress(array $address): InvoiceService
	{
		$this->billingAddress = $this->parseAddress('billing', $address);

		return $this;
	}

	public function shippingAddress(array $address): InvoiceService
	{
		$this->shippingAddress = $this->parseAddress('shipping', $address);

		return $this;
	}

	public function customField(string $name, string $value): InvoiceService
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

	public function addNote(string $note): InvoiceService
	{
		$this->note = $note;

		return $this;
	}

	public function view(Invoice $invoice, array $data = []): \Illuminate\Contracts\View\View
	{
		return View::make('laravel-invoice::invoices.invoice', array_merge($data, [
			'invoice' => $invoice,
		]));
	}

	public function saveAndView(array $data = []): \Illuminate\Contracts\View\View
	{
		$invoice = $this->save();

		return $this->view($invoice);
	}

	protected function parseAddress($type, $address): array
	{
		if (!Arr::exists($address, 'name') || !Arr::exists($address, 'line_1')) {
			throw new \Exception(sprintf(
				"The %s address requires a name and at least one address line", $type
			), 1);
		}

		return [
			'address_type' => $type,
			'name' => $address['name'],
			'line_1' => $address['line_1'],
			'line_2' => $address['line_2'] ?? null,
			'line_3' => $address['line_3'] ?? null,
		];
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
				// Because amounts are in cents, to get the percent value instead 
				// of divide by 100, we divide by 10000 to convert amount in decimals 
				// format an get the percent value 
				? $amount * $this->lines['discount']['amount'] / 10000 
				: $this->lines['discount']['amount'];
		}

		if (Arr::exists($this->lines, 'tax')) {
			// Because amounts are in cents, to get the percent value instead 
			// of divide by 100, we divide by 10000 to convert amount in decimals 
			// format an get the percent value 
			$amount += $amount * ($this->lines['tax']['amount'] / 10000);
		}

		return $amount;
	}

	protected function resetInvoiceService()
	{
		$this->customer = null;
		$this->invoiceable = null;
		$this->number = null;
		$this->currency = config('invoice.currency');
		$this->date = now()->format('Y-m-d');
		$this->lines = array();
		$this->billingAddress = array();
		$this->shippingAddress = array();
		$this->customFields = array();
		$this->note = null;
	}
}
