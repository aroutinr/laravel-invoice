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
}
