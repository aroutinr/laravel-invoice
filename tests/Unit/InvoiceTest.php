<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\Invoice;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function invoice_need_at_least_one_invoice_line_to_be_created()
	{
		$this->expectException(\Exception::class);

		Invoice::for($this->customer, $this->invoiceable)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You must add at least one invoice line to the invoice');

		$this->assertDatabaseCount('invoices', 0);
	}

	/** @test */
	public function calculate_invoice_amount()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->save();

		$this->assertSame(30000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->save();

		$this->assertSame(25000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->percentDiscountLine('A Cool Discout', 10)
			->save();

		$this->assertSame(27000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_tax()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->taxLine('Tax 3%', 3)
			->save();

		$this->assertSame(30900, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount_and_tax()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->taxLine('Tax 3%', 3)
			->save();

		$this->assertSame(25750, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount_and_tax()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->percentDiscountLine('A Cool Discout', 10)
			->taxLine('Tax 3%', 3)
			->save();

		$this->assertSame(27810, $invoice->amount);
	}

	/** @test */
	public function can_set_invoice_number()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceNumber('INVOICE-1234');

		$this->assertEquals('INVOICE-1234', $invoice->number);
	}

	/** @test */
	public function currency_can_be_changed()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('EUR');

		$this->assertEquals('EUR', $invoice->currency);
	}

	/** @test */
	public function date_can_be_changed()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceDate('2021-03-01');

		$this->assertEquals('2021-03-01', $invoice->date);
	}

	/** @test */
	public function currency_can_only_have_three_characters()
	{
		$this->expectException(\Exception::class);

		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('USDS');
		$this->assertDontSeeText('USDS', $this->invoice->currency);

		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('US');
		$this->assertDontSeeText('US', $this->invoice->currency);

		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('$');
		$this->assertDontSeeText('$', $this->invoice->currency);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The currency should only be 3 characters long');
	}

	/** @test */
	public function add_custom_fields_to_invoice()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->customField('Origin', 'Houston');

		$this->assertEquals('Origin', $invoice->customFields[0]['name']);
		$this->assertEquals('Houston', $invoice->customFields[0]['value']);
	}

	/** @test */
	public function only_4_custom_fields_allowed()
	{
		$this->expectException(\Exception::class);

		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->customField('Invoice Terms', 'Due on receipt')
			->customField('Origin', 'Houston')
			->customField('Destination', 'Miami')
			->customField('Service Type', 'Ground')
			->customField('Carrier', 'UPS');

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You can add a maximum of 4 custom fields');
	}

	/** @test */
	public function custom_fields_are_casted_to_array()
	{
		$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->customField('Origin', 'Houston')
			->save();

		$this->assertEquals('Origin', $invoice->custom_fields[0]['name']);
		$this->assertEquals('Houston', $invoice->custom_fields[0]['value']);
	}

    /** @test */
    public function can_render_a_invoice_view()
    {
    	$invoice = Invoice::for($this->customer, $this->invoiceable)
			->customField('Origin', 'Houston')
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->taxLine('Tax 3%', 3)
			->saveAndView()
			->render(); // if view cannot be rendered will fail the test

        $this->assertTrue(true);
    }

    /** @test */
    public function test_invoice_balance_attribute()
    {
    	$invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->assertSame(10000, $invoice->balance);
    }
}
