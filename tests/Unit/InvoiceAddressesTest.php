<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\CreateInvoice;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceAddressesTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function require_name_and_line_1_to_add_billing_address()
	{
		$this->expectException(\Exception::class);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->billingAddress([]);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The billing address requires a name and at least one address line');
	}

	/** @test */
	public function minimun_data_to_add_billing_address()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->billingAddress([
				'name' => 'Billing Name',
				'line_1' => 'Billing Line 1',
			]);

		$this->assertEquals('billing', $invoice->billingAddress['address_type']);
		$this->assertEquals('Billing Name', $invoice->billingAddress['name']);
		$this->assertEquals('Billing Line 1', $invoice->billingAddress['line_1']);
		$this->assertNull($invoice->billingAddress['line_2']);
		$this->assertNull($invoice->billingAddress['line_3']);
	}

	/** @test */
	public function can_add_billing_address_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->billingAddress([
				'name' => 'Billing Name',
				'line_1' => 'Billing Line 1',
				'line_2' => 'Billing Line 2',
				'line_3' => 'Billing Line 3',
			]);

		$this->assertEquals('billing', $invoice->billingAddress['address_type']);
		$this->assertEquals('Billing Name', $invoice->billingAddress['name']);
		$this->assertEquals('Billing Line 1', $invoice->billingAddress['line_1']);
		$this->assertEquals('Billing Line 2', $invoice->billingAddress['line_2']);
		$this->assertEquals('Billing Line 3', $invoice->billingAddress['line_3']);
	}

	/** @test */
	public function require_name_and_line_1_to_add_shipping_address()
	{
		$this->expectException(\Exception::class);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->shippingAddress([]);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The shipping address requires a name and at least one address line');
	}

	/** @test */
	public function minimun_data_to_add_shipping_address()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->shippingAddress([
				'name' => 'Billing Name',
				'line_1' => 'Billing Line 1',
			]);

		$this->assertEquals('shipping', $invoice->shippingAddress['address_type']);
		$this->assertEquals('Billing Name', $invoice->shippingAddress['name']);
		$this->assertEquals('Billing Line 1', $invoice->shippingAddress['line_1']);
		$this->assertNull($invoice->shippingAddress['line_2']);
		$this->assertNull($invoice->shippingAddress['line_3']);
	}

	/** @test */
	public function can_add_shipping_address_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->shippingAddress([
				'name' => 'Shipping Name',
				'line_1' => 'Shipping Line 1',
				'line_2' => 'Shipping Line 2',
				'line_3' => 'Shipping Line 3',
			]);

		$this->assertEquals('shipping', $invoice->shippingAddress['address_type']);
		$this->assertEquals('Shipping Name', $invoice->shippingAddress['name']);
		$this->assertEquals('Shipping Line 1', $invoice->shippingAddress['line_1']);
		$this->assertEquals('Shipping Line 2', $invoice->shippingAddress['line_2']);
		$this->assertEquals('Shipping Line 3', $invoice->shippingAddress['line_3']);
	}

	/** @test */
	public function can_save_invoice_with_billing_and_shipping_address()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->billingAddress([
				'name' => 'Billing Name',
				'line_1' => 'Billing Line 1',
				'line_2' => 'Billing Line 2',
				'line_3' => 'Billing Line 3',
			])
			->shippingAddress([
				'name' => 'Shipping Name',
				'line_1' => 'Shipping Line 1',
				'line_2' => 'Shipping Line 2',
				'line_3' => 'Shipping Line 3',
			])
			->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_addresses', 2);
		$this->assertEquals($this->customer->id, $invoice->billingAddress->customer_id);
	}
}
