<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TraitsTest extends TestCase
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
        
        $this->invoice->addInvoiceLine('Some description', 1, 10000);

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

        $this->invoice->save();
    }

	/** @test */
	public function customer_has_invoices_trait_can_read_invoices()
	{
        $customer = User::find(1);

        $this->assertCount(1, $customer->invoices);
	}

    /** @test */
    public function has_invoice_trait_can_read_invoices()
    {
        $service = Service::find(1);

        $this->assertCount(1, $service->invoices);
    }

    /** @test */
    public function has_addresses_trait_can_read_addresses()
    {
        $customer = User::find(1);

        $this->assertCount(2, $customer->addresses);
    }

    /** @test */
    public function has_addresses_trait_can_read_billing_address()
    {
        $customer = User::find(1);

        $this->assertSame('Billing Name', $customer->billingAddress->name);
        $this->assertSame('Billing Line 1', $customer->billingAddress->line_1);
        $this->assertSame('Billing Line 2', $customer->billingAddress->line_2);
        $this->assertSame('Billing Line 3', $customer->billingAddress->line_3);
    }

    /** @test */
    public function has_addresses_trait_can_read_shipping_address()
    {
        $customer = User::find(1);

        $this->assertSame('Shipping Name', $customer->shippingAddress->name);
        $this->assertSame('Shipping Line 1', $customer->shippingAddress->line_1);
        $this->assertSame('Shipping Line 2', $customer->shippingAddress->line_2);
        $this->assertSame('Shipping Line 3', $customer->shippingAddress->line_3);
    }
}
