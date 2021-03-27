<?php

namespace AroutinR\Invoice\Tests\Unit;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Services\PaymentService;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;
use AroutinR\Invoice\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentTest extends TestCase
{
	use RefreshDatabase;

    protected $invoice;
    protected $payment;

    public function setUp(): void
    {
        parent::setUp();

        $customer = factory(User::class)->create();

        $invoiceable = factory(Service::class)->create();

        $invoice = new InvoiceService($customer, $invoiceable);
		$invoice->addInvoiceLine('Some description', 1, 10000);
		$this->invoice = $invoice->save();

		$this->payment = new PaymentService($this->invoice);
    }

	/** @test */
	public function can_create_a_payment()
	{
		$this->payment->setAmount(10000);

		$payment = $this->payment->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame($this->invoice->id, $payment->invoice_id);
	}

	/** @test */
	public function can_create_multiple_payments()
	{
		$this->payment->setAmount(5000);

		$payment = $this->payment->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(5000, $this->invoice->balance);

		$anotherPayment = new PaymentService($this->invoice);

		$anotherPayment->setAmount(5000);

		$payment = $anotherPayment->save();

		$this->assertDatabaseCount('payments', 2);
		$this->assertSame(0, $this->invoice->balance);
	}

	/** @test */
	public function payment_amount_cannot_be_more_than_the_invoice_amount()
	{
		$this->expectException(\Exception::class);

		$this->payment->setAmount(20000);

		$this->payment->save();

		$this->expectException('Exception');
		$this->expectExceptionCode(1);
		$this->expectExceptionMessage('The payment amount cannot be higher than the invoice amount');

		$this->assertDatabaseCount('payments', 0);
	}

	/** @test */
	public function payment_amount_can_be_less_than_the_invoice_amount()
	{
		$payment = new PaymentService($this->invoice);

		$this->payment->setAmount(3000);

		$this->payment->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(7000, $this->invoice->balance);
	}

	/** @test */
	public function can_create_a_payment_with_all_data()
	{
		$this->payment->setAmount(10000);
		$this->payment->setNumber('PAYMENT-123');
		$this->payment->setMethod('Check');
		$this->payment->setReference('Check # 001122');

		$payment = $this->payment->save();

		$this->assertDatabaseCount('payments', 1);
		$this->assertSame(10000, $payment->amount);
		$this->assertSame('PAYMENT-123', $payment->number);
		$this->assertSame('Check', $payment->method);
		$this->assertSame('Check # 001122', $payment->reference);
	}
}
