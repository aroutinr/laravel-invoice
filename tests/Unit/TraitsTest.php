<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\CreateInvoice;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TraitsTest extends TestCase
{
	use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        CreateInvoice::for($this->customer, $this->invoiceable)
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
    }

	/** @test */
	public function customer_has_invoices_trait_can_read_invoices()
	{
        $this->assertCount(1, $this->customer->invoices);
	}

    /** @test */
    public function has_invoice_trait_can_read_invoices()
    {
        $this->assertCount(1, $this->invoiceable->invoices);
    }

    /** @test */
    public function has_addresses_trait_can_read_addresses()
    {
        $this->assertCount(2, $this->customer->addresses);
    }

    /** @test */
    public function has_addresses_trait_can_read_billing_address()
    {
        $this->assertSame('Billing Name', $this->customer->billingAddress->name);
        $this->assertSame('Billing Line 1', $this->customer->billingAddress->line_1);
        $this->assertSame('Billing Line 2', $this->customer->billingAddress->line_2);
        $this->assertSame('Billing Line 3', $this->customer->billingAddress->line_3);
    }

    /** @test */
    public function has_addresses_trait_can_read_shipping_address()
    {
        $this->assertSame('Shipping Name', $this->customer->shippingAddress->name);
        $this->assertSame('Shipping Line 1', $this->customer->shippingAddress->line_1);
        $this->assertSame('Shipping Line 2', $this->customer->shippingAddress->line_2);
        $this->assertSame('Shipping Line 3', $this->customer->shippingAddress->line_3);
    }
}
