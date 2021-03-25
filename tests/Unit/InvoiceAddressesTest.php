<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceAddressesTest extends TestCase
{
	use RefreshDatabase;

    protected $customer;
    protected $invoiceable;
    protected $invoice;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = factory(User::class)->create();

        $this->invoiceable = factory(Service::class)->create();

        $this->invoice = new InvoiceService($this->customer, $this->invoiceable);
    }

	/** @test */
	public function can_add_billing_address_to_the_invoice()
	{
		$this->invoice->addBillingAddress([
			'name' => 'Billing Name',
			'line_1' => 'Billing Line 1',
			'line_2' => 'Billing Line 2',
			'line_3' => 'Billing Line 3',
		]);

		$this->assertEquals('billing', $this->invoice->billingAddress['address_type']);
		$this->assertEquals('Billing Name', $this->invoice->billingAddress['name']);
		$this->assertEquals('Billing Line 1', $this->invoice->billingAddress['line_1']);
		$this->assertEquals('Billing Line 2', $this->invoice->billingAddress['line_2']);
		$this->assertEquals('Billing Line 3', $this->invoice->billingAddress['line_3']);
	}

	/** @test */
	public function can_add_shipping_address_to_the_invoice()
	{
		$this->invoice->addShippingAddress([
			'name' => 'Shipping Name',
			'line_1' => 'Shipping Line 1',
			'line_2' => 'Shipping Line 2',
			'line_3' => 'Shipping Line 3',
		]);

		$this->assertEquals('shipping', $this->invoice->shippingAddress['address_type']);
		$this->assertEquals('Shipping Name', $this->invoice->shippingAddress['name']);
		$this->assertEquals('Shipping Line 1', $this->invoice->shippingAddress['line_1']);
		$this->assertEquals('Shipping Line 2', $this->invoice->shippingAddress['line_2']);
		$this->assertEquals('Shipping Line 3', $this->invoice->shippingAddress['line_3']);
	}

	/** @test */
	public function can_save_invoice_with_billing_and_shipping_address()
	{
		$this->invoice->addBillingAddress([
			'name' => 'Billing Name',
			'line_1' => 'Billing Line 1',
			'line_2' => 'Billing Line 2',
			'line_3' => 'Billing Line 3',
		]);

		$this->invoice->addShippingAddress([
			'name' => 'Shipping Name',
			'line_1' => 'Shipping Line 1',
			'line_2' => 'Shipping Line 2',
			'line_3' => 'Shipping Line 3',
		]);

		$invoice = $this->invoice->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_addresses', 2);
		$this->assertEquals($this->customer->id, $invoice->billingAddress->customer_id);
	}
}
