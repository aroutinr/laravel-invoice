<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceLinesTest extends TestCase
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
	public function can_add_invoice_line_to_the_invoice()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);

		$this->assertEquals('invoice', $this->invoice->lines[0]['line_type']);
		$this->assertEquals('Some description', $this->invoice->lines[0]['description']);
		$this->assertEquals(1, $this->invoice->lines[0]['quantity']);
		$this->assertEquals(10000, $this->invoice->lines[0]['amount']);
	}

	/** @test */
	public function can_add_fixed_discount_line_to_the_invoice()
	{
		$this->invoice->addFixedDiscountLine('A Cool Discout', 100);

		$this->assertEquals('discount', $this->invoice->lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $this->invoice->lines['discount']['description']);
		$this->assertEquals(100, $this->invoice->lines['discount']['amount']);
	}

	/** @test */
	public function can_add_percent_based_discount_line_to_the_invoice()
	{
		$this->invoice->addPercentDiscountLine('A Cool Discout', 10);

		$this->assertEquals('discount', $this->invoice->lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $this->invoice->lines['discount']['description']);
		$this->assertEquals(10, $this->invoice->lines['discount']['amount']);
		$this->assertTrue($this->invoice->lines['discount']['percent_based']);
	}

	/** @test */
	public function only_one_discount_line_can_be_added()
	{
		$this->invoice->addPercentDiscountLine('A Cool Discout', 10);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('Only one discount line can be added');

		$this->invoice->addFixedDiscountLine('Another Cool Discout', 1000);

		$this->assertEquals('discount', $this->invoice->lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $this->invoice->lines['discount']['description']);
		$this->assertEquals(10, $this->invoice->lines['discount']['amount']);
		$this->assertTrue($this->invoice->lines['discount']['percent_based']);
	}

	/** @test */
	public function can_add_tax_line_to_the_invoice()
	{
		$this->invoice->addTaxLine('Tax 3%', 3);

		$this->assertEquals('tax', $this->invoice->lines['tax']['line_type']);
		$this->assertEquals('Tax 3%', $this->invoice->lines['tax']['description']);
		$this->assertEquals(3, $this->invoice->lines['tax']['amount']);
		$this->assertTrue($this->invoice->lines['tax']['percent_based']);
	}

	/** @test */
	public function only_one_tax_line_can_be_added()
	{
		$this->invoice->addTaxLine('Tax 3%', 3);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('Only one tax line can be added');

		$this->invoice->addTaxLine('Tax 7%', 7);

		$this->assertEquals('tax', $this->invoice->lines['tax']['line_type']);
		$this->assertEquals('Tax 3%', $this->invoice->lines['tax']['description']);
		$this->assertEquals(3, $this->invoice->lines['tax']['amount']);
		$this->assertTrue($this->invoice->lines['tax']['percent_based']);
	}

	/** @test */
	public function can_add_invoice_line_to_the_invoice_and_create_the_invoice()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);

		$this->assertEquals('invoice', $this->invoice->lines[0]['line_type']);
		$this->assertEquals('Some description', $this->invoice->lines[0]['description']);
		$this->assertEquals(1, $this->invoice->lines[0]['quantity']);
		$this->assertEquals(10000, $this->invoice->lines[0]['amount']);

		$invoice = $this->invoice->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_lines', 1);
		$this->assertNotTrue($invoice->lines()->first()->percent_based);
	}
}
