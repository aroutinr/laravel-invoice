<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\CreateInvoice;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceLinesTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function can_add_invoice_line_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000);

		$this->assertEquals('invoice', $invoice->lines[0]['line_type']);
		$this->assertEquals('Some description', $invoice->lines[0]['description']);
		$this->assertEquals(1, $invoice->lines[0]['quantity']);
		$this->assertEquals(10000, $invoice->lines[0]['amount']);
	}

	/** @test */
	public function can_add_multiple_invoice_lines_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLines([
				[
					'quantity' => 1, 
					'amount' => 10000,
					'description' => 'Some description',
				],
				[
					'quantity' => 1, 
					'amount' => 20000,
					'description' => 'Another description'
				],
				[
					'quantity' => 2, 
					'amount' => 30000,
					'description' => 'Final description'
				]
			]);

		$this->assertEquals('invoice', $invoice->lines[0]['line_type']);
		$this->assertEquals(10000, $invoice->lines[0]['amount']);
		$this->assertEquals('Another description', $invoice->lines[1]['description']);
		$this->assertEquals(2, $invoice->lines[2]['quantity']);
	}

	/** @test */
	public function can_add_fixed_discount_line_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->fixedDiscountLine('A Cool Discout', 100);

		$this->assertEquals('discount', $invoice->lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $invoice->lines['discount']['description']);
		$this->assertEquals(100, $invoice->lines['discount']['amount']);
	}

	/** @test */
	public function can_add_percent_based_discount_line_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->percentDiscountLine('A Cool Discout', 1000);

		$this->assertEquals('discount', $invoice->lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $invoice->lines['discount']['description']);
		$this->assertEquals(1000, $invoice->lines['discount']['amount']);
		$this->assertTrue($invoice->lines['discount']['percent_based']);
	}

	/** @test */
	public function only_one_discount_line_can_be_added()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->percentDiscountLine('A Cool Discout', 1000);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('Only one discount line can be added');

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->fixedDiscountLine('Another Cool Discout', 1000);

		$this->assertEquals('discount', $lines['discount']['line_type']);
		$this->assertEquals('A Cool Discout', $lines['discount']['description']);
		$this->assertEquals(10, $lines['discount']['amount']);
		$this->assertTrue($lines['discount']['percent_based']);
	}

	/** @test */
	public function can_add_tax_line_to_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->taxLine('Tax 3%', 300);

		$this->assertEquals('tax', $invoice->lines['tax']['line_type']);
		$this->assertEquals('Tax 3%', $invoice->lines['tax']['description']);
		$this->assertEquals(300, $invoice->lines['tax']['amount']);
		$this->assertTrue($invoice->lines['tax']['percent_based']);
	}

	/** @test */
	public function only_one_tax_line_can_be_added()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->taxLine('Tax 3%', 300);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('Only one tax line can be added');

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->taxLine('Tax 7%', 700);

		$this->assertEquals('tax', $invoice->lines['tax']['line_type']);
		$this->assertEquals('Tax 3%', $invoice->lines['tax']['description']);
		$this->assertEquals(3, $invoice->lines['tax']['amount']);
		$this->assertTrue($invoice->lines['tax']['percent_based']);
	}

	/** @test */
	public function can_add_invoice_line_to_the_invoice_and_create_the_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->assertEquals('invoice', $invoice->lines[0]['line_type']);
		$this->assertEquals('Some description', $invoice->lines[0]['description']);
		$this->assertEquals(1, $invoice->lines[0]['quantity']);
		$this->assertEquals(10000, $invoice->lines[0]['amount']);

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_lines', 1);
		$this->assertNotTrue($invoice->lines()->first()->percent_based);
	}

	/** @test */
	public function can_read_invoice_lines()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->percentDiscountLine('A Cool Discout', 1000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_lines', 3);
		$this->assertEquals('Some description', $invoice->lines->first()->description);
		$this->assertEquals('A Cool Discout', $invoice->discount->description);
		$this->assertEquals('Tax 3%', $invoice->tax->description);
	}

	/** @test */
	public function can_read_invoice_lines_amount()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 10000)
			->percentDiscountLine('A Cool Discout', 1000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_lines', 4);
		$this->assertEquals(20000, $invoice->linesAmount);
		$this->assertEquals(18540, $invoice->amount);
	}

	/** @test */
	public function test_invoice_custom_attributes()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertDatabaseCount('invoice_lines', 4);
		$this->assertEquals(30000, $invoice->linesAmount);
		$this->assertEquals(25000, $invoice->linesAmountWithDiscount);
		$this->assertEquals(5000, $invoice->discountAmount);
		$this->assertEquals(750, $invoice->taxAmount);
	}
}
