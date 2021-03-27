<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Facades\Invoice;
use AroutinR\Invoice\Facades\Payment;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	public function can_create_a_payment()
	{
        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = Payment::for($invoice)
			->paymentAmount(10000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame($invoice->id, $payment->invoice_id);
	}

	/** @test */
	public function can_create_multiple_payments()
	{
        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		Payment::for($invoice)
			->paymentAmount(5000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(5000, $invoice->balance);

		Payment::for($invoice)
			->paymentAmount(5000)
			->save();

		$this->assertDatabaseCount('payments', 2);
		$this->assertSame(0, $invoice->balance);
	}

	/** @test */
	public function payment_amount_cannot_be_more_than_the_invoice_amount()
	{
		$this->expectException(\Exception::class);

        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		Payment::for($invoice)
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
        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		Payment::for($invoice)
			->paymentAmount(3000)
			->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(7000, $invoice->balance);
	}

	/** @test */
	public function can_create_a_payment_with_all_data()
	{
        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = Payment::for($invoice)
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
        $invoice = Invoice::for($this->customer, $this->invoiceable)
			->invoiceLine('Some description', 1, 10000)
			->save();

		$payment = Payment::for($invoice)
			->paymentAmount(10000)
			->paymentNumber('PAYMENT-123')
			->paymentMethod('Check')
			->paymentReference('Check # 001122')
			->saveAndView()
			->render(); // if view cannot be rendered will fail the test

        $this->assertTrue(true);
    }
}
