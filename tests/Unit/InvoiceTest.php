<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
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
	public function invoice_need_at_leas_on_invoice_line_to_be_created()
	{
		$this->expectException(\Exception::class);

		$this->invoice->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You must add at least one invoice line to the invoice');

		$this->assertDatabaseCount('invoices', 0);
	}

	/** @test */
	public function calculate_invoice_amount()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);

		$invoice = $this->invoice->save();

		$this->assertSame(30000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addFixedDiscountLine('A Cool Discout', 5000);

		$invoice = $this->invoice->save();

		$this->assertSame(25000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addPercentDiscountLine('A Cool Discout', 10);

		$invoice = $this->invoice->save();

		$this->assertSame(27000, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_tax()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addTaxLine('Tax 3%', 3);

		$invoice = $this->invoice->save();

		$this->assertSame(30900, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_fixed_discount_and_tax()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addFixedDiscountLine('A Cool Discout', 5000);
		$this->invoice->addTaxLine('Tax 3%', 3);

		$invoice = $this->invoice->save();

		$this->assertSame(25750, $invoice->amount);
	}

	/** @test */
	public function calculate_invoice_amount_with_percent_discount_and_tax()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addPercentDiscountLine('A Cool Discout', 10);
		$this->invoice->addTaxLine('Tax 3%', 3);

		$invoice = $this->invoice->save();

		$this->assertSame(27810, $invoice->amount);
	}

	/** @test */
	public function can_set_invoice_number()
	{
		$this->invoice->setNumber('INVOICE-1234');

		$this->assertEquals('INVOICE-1234', $this->invoice->number);
	}

	/** @test */
	public function currency_can_be_changed()
	{
		$this->invoice->setCurrency('EUR');

		$this->assertEquals('EUR', $this->invoice->currency);
	}

	/** @test */
	public function date_can_be_changed()
	{
		$this->invoice->setDate('2021-03-01');

		$this->assertEquals('2021-03-01', $this->invoice->date);
	}

	/** @test */
	public function currency_and_date_can_be_changed_in_once()
	{
		$this->invoice->setCurrency('EUR')->setDate('2021-03-01');

		$this->assertEquals('EUR', $this->invoice->currency);
		$this->assertEquals('2021-03-01', $this->invoice->date);
	}

	/** @test */
	public function currency_can_only_have_three_characters()
	{
		$this->expectException(\Exception::class);

		$this->invoice->setCurrency('USDS');
		$this->assertDontSeeText('USDS', $this->invoice->currency);

		$this->invoice->setCurrency('US');
		$this->assertDontSeeText('US', $this->invoice->currency);

		$this->invoice->setCurrency('$');
		$this->assertDontSeeText('$', $this->invoice->currency);

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The currency should only be 3 characters long');
	}

	/** @test */
	public function add_custom_fields_to_invoice()
	{
		$this->invoice->addCustomField('Origin', 'Houston');

		$this->assertEquals('Origin', $this->invoice->customFields[0]['name']);
		$this->assertEquals('Houston', $this->invoice->customFields[0]['value']);
	}

	/** @test */
	public function only_4_custom_fields_allowed()
	{
		$this->expectException(\Exception::class);

		$this->invoice->addCustomField('Invoice Terms', 'Due on receipt');
		$this->invoice->addCustomField('Origin', 'Houston');
		$this->invoice->addCustomField('Destination', 'Miami');
		$this->invoice->addCustomField('Service Type', 'Ground');
		$this->invoice->addCustomField('Carrier', 'UPS');

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You can add a maximum of 4 custom fields');
	}

	/** @test */
	public function custom_fields_are_casted_to_array()
	{
		$this->invoice->addInvoiceLine('Some description', 1, 10000);

		$this->invoice->addCustomField('Origin', 'Houston');

		$invoice = $this->invoice->save();

		$this->assertEquals('Origin', $invoice->custom_fields[0]['name']);
		$this->assertEquals('Houston', $invoice->custom_fields[0]['value']);
	}

    /** @test */
    public function can_render_a_invoice_view()
    {
		$this->invoice->addCustomField('Origin', 'Houston');
		$this->invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice->addInvoiceLine('Another description', 1, 20000);
		$this->invoice->addFixedDiscountLine('A Cool Discout', 5000);
		$this->invoice->addTaxLine('Tax 3%', 3);

		$invoice = $this->invoice->save();

        $view = $this->invoice->view();

        $rendered = $view->render(); // if view cannot be rendered will fail the test

        $this->assertTrue(true);
    }
}
