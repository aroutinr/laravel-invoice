<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\CreateInvoice;
use AroutinR\Invoice\Facades\CreatePayment;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function can_create_a_payment()
	{
        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = CreatePayment::for($invoice)
			->paymentAmount(10000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame($invoice->id, $payment->invoice_id);
	}

	/** @test */
	public function payment_needs_a_invoice_model()
	{
		$this->expectException(\Exception::class);

		CreatePayment::paymentAmount(10000)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('You must add a Invoice model');

		$this->assertDatabaseCount('payments', 0);
	}

	/** @test */
	public function can_create_multiple_payments()
	{
        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		CreatePayment::for($invoice)
			->paymentAmount(5000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(5000, $invoice->balance);

		CreatePayment::for($invoice)
			->paymentAmount(5000)
			->save();

		$this->assertDatabaseCount('payments', 2);
		$this->assertSame(0, $invoice->balance);
	}

	/** @test */
	public function payment_amount_cannot_be_more_than_the_invoice_amount()
	{
		$this->expectException(\Exception::class);

        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		CreatePayment::for($invoice)
			->paymentAmount(20000)
			->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The payment amount cannot be higher than the invoice amount');

		$this->assertDatabaseCount('payments', 0);
	}

	/** @test */
	public function payment_amount_can_be_less_than_the_invoice_amount()
	{
        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		CreatePayment::for($invoice)
			->paymentAmount(3000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(7000, $invoice->balance);
	}

	/** @test */
	public function can_create_a_payment_with_all_data()
	{
        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = CreatePayment::for($invoice)
			->paymentAmount(10000)
			->paymentNumber('PAYMENT-123')
			->paymentMethod('Check')
			->paymentReference('Check # 001122')
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(10000, $payment->amount);
		$this->assertSame('PAYMENT-123', $payment->number);
		$this->assertSame('Check', $payment->method);
		$this->assertSame('Check # 001122', $payment->reference);
	}

    /** @test */
    public function can_render_a_payment_view()
    {
        $invoice = CreateInvoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = CreatePayment::for($invoice)
			->paymentAmount(10000)
			->paymentNumber('PAYMENT-123')
			->paymentMethod('Check')
			->paymentReference('Check # 001122')
			->saveAndView()
			->render(); // if view cannot be rendered will fail the test

        $this->assertTrue(true);
    }
}
