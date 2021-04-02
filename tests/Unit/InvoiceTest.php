<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\CreateInvoice;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function invoice_needs_a_customer_and_invoiceable_model()
	{
		$this->expectException(\Exception::class);

		CreateInvoice::invoiceLine('Some description', 1, 10000)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You must add a Customer and Invoiceable model');

		$this->assertDatabaseCount('invoices', 0);
	}

	/** @test */
	public function invoice_customer_and_invoiceable_has_to_be_model_instances()
	{
		$this->expectException('TypeError');

		CreateInvoice::for('Customer', 'Invoiceable')
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->assertDatabaseCount('invoices', 0);
	}

	/** @test */
	public function invoice_need_at_least_one_invoice_line_to_be_created()
	{
		$this->expectException(\Exception::class);

		CreateInvoice::for($this->customer, $this->invoiceable)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You must add at least one invoice line to the invoice');

		$this->assertDatabaseCount('invoices', 0);
	}

	/** @test */
	public function calculate_invoice_amount()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->save();

		$this->assertSame(30000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->save();

		$this->assertSame(25000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->percentDiscountLine('A Cool Discout', 1000)
			->save();

		$this->assertSame(27000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_tax()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertSame(30900, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount_and_tax()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertSame(25750, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount_and_tax()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->percentDiscountLine('A Cool Discout', 1000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertSame(27810, $invoice->amount);
	}

	/** @test */
	public function can_set_invoice_number()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceNumber('INVOICE-1234');

		$this->assertEquals('INVOICE-1234', $invoice->number);
	}

	/** @test */
	public function currency_can_be_changed()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('EUR');

		$this->assertEquals('EUR', $invoice->currency);
	}

	/** @test */
	public function date_can_be_changed()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceDate('2021-03-01');

		$this->assertEquals('2021-03-01', $invoice->date);
	}

	/** @test */
	public function can_add_due_date()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceDueDate('2021-04-15');

		$this->assertEquals('2021-04-15', $invoice->dueDate);
	}

	/** @test */
	public function due_date_is_saved_to_the_database()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->invoiceDueDate('2021-04-15')
			->save();

		$this->assertDatabaseCount('invoices', 1);
		$this->assertEquals('2021-04-15', $invoice->due_date);
	}

	/** @test */
	public function currency_can_only_have_three_characters()
	{
		$this->expectException(\Exception::class);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('USDS');
		$this->assertDontSeeText('USDS', $this->invoice->currency);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('US');
		$this->assertDontSeeText('US', $this->invoice->currency);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceCurrency('$');
		$this->assertDontSeeText('$', $this->invoice->currency);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The currency should only be 3 characters long');
	}

	/** @test */
	public function add_custom_fields_to_invoice()
	{
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->customField('Origin', 'Houston');

		$this->assertEquals('Houston', $invoice->customFields['Origin']);
	}

	/** @test */
	public function only_4_custom_fields_allowed()
	{
		$this->expectException(\Exception::class);

		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
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
		$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->customField('Origin', 'Houston')
			->save();

		$this->assertEquals('Houston', $invoice->custom_fields['Origin']);
	}

    /** @test */
    public function can_render_a_invoice_view()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceDueDate('2021-04-15')
			->customField('Origin', 'Houston')
			->invoiceLine('Some description', 1, 10000)
			->invoiceLine('Another description', 1, 20000)
			->fixedDiscountLine('A Cool Discout', 5000)
			->taxLine('Tax 3%', 300)
			->saveAndView()
			->render(); // if view cannot be rendered will fail the test

        $this->assertTrue(true);
    }

    /** @test */
    public function test_invoice_balance_attribute()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->assertSame(10000, $invoice->balance);
    }

    /** @test */
    public function test_invoice_discount_amount_attribute_with_percent_based_discount()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->percentDiscountLine('A Cool Discout', 2000)
			->save();

		$this->assertSame(8000, $invoice->balance);
    }

    /** @test */
    public function test_invoice_tax_amount_attribute()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->taxLine('Tax 3%', 300)
			->save();

		$this->assertSame(10300, $invoice->balance);
    }

    /** @test */
    public function test_invoice_discount_attribute_exception()
    {
		$this->expectException(\Exception::class);

    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('This invoice does not have discounts');

		$this->assertNull($invoice->discountAmount);
    }

    /** @test */
    public function test_invoice_tax_attribute_exception()
    {
		$this->expectException(\Exception::class);

    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('This invoice does not have taxes');

		$this->assertNull($invoice->taxAmount);
    }

    /** @test */
    public function can_add_notes_to_invoice()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->addNote('This is a note for the invoice')
			->save();

		$this->assertSame('This is a note for the invoice', $invoice->note);
    }

    /** @test */
    public function invoice_services_properties_are_reseted_after_save()
    {
    	$invoice = CreateInvoice::for($this->customer, $this->invoiceable);
		$invoice->invoiceNumber('INVOICE-1234');
		$invoice->addNote('This is a note for the invoice');
		$invoice->customField('Origin', 'Houston');
		$invoice->invoiceLine('Some description', 1, 10000);
		$invoice->invoiceLine('Another description', 1, 20000);
		$invoice->fixedDiscountLine('A Cool Discout', 5000);
		$invoice->taxLine('Tax 3%', 300);
		$invoice->save();

		$this->assertNull($invoice->customer);
		$this->assertNull($invoice->invoiceable);
		$this->assertNull($invoice->number);
		$this->assertNotNull($invoice->currency);
		$this->assertNotNull($invoice->date);
		$this->assertNull($invoice->note);
		$this->assertEmpty($invoice->lines);
		$this->assertEmpty($invoice->billingAddress);
		$this->assertEmpty($invoice->shippingAddress);
		$this->assertEmpty($invoice->customFields);
    }
}
