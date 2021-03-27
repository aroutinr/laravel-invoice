<?php

namespace AroutinR\Invoice\Services;

use AroutinR\Invoice\Interfaces\PaymentServiceInterface;
use AroutinR\Invoice\Models\Invoice;
use AroutinR\Invoice\Models\Payment;
use Illuminate\Support\Facades\View;

class PaymentService implements PaymentServiceInterface
{
	public $invoice;
	public $payment;
	public $date;
	public $amount;
	public $number;
	public $method;
	public $reference;

	public function __construct()
	{
		$this->paymentDate(now()->format('Y-m-d'));
	}

	public function for(Invoice $invoice): PaymentService
	{
		$this->invoice = $invoice;

		return $this;
	}

	public function save(): Payment
	{
		if (!$this->invoice) {
			throw new \Exception('You must add a Invoice model', 1);
		}

		if ($this->amount > $this->invoice->balance) {
			throw new \Exception("The payment amount cannot be higher than the invoice amount", 1);
		}

		$this->payment = $this->invoice->payments()->create([
			'date' => $this->date,
			'amount' => $this->amount,
			'number' => $this->number,
			'method' => $this->method,
			'reference' => $this->reference,
		]);

		return $this->payment;
	}

	public function paymentDate(string $date): PaymentService
	{
		$this->date = $date;

		return $this;
	}

	public function paymentAmount(int $amount): PaymentService
	{
		$this->amount = $amount;

		return $this;
	}

	public function paymentNumber(string $number): PaymentService
	{
		$this->number = $number;

		return $this;
	}

	public function paymentMethod(string $method): PaymentService
	{
		$this->method = $method;

		return $this;
	}

	public function paymentReference(string $reference): PaymentService
	{
		$this->reference = $reference;

		return $this;
	}

    public function view(array $data = []): \Illuminate\Contracts\View\View
    {
        return View::make('laravel-invoice::payments.payment', array_merge($data, [
            'payment' => $this->payment,
        ]));
    }

    public function saveAndView(array $data = []): \Illuminate\Contracts\View\View
    {
    	$this->save();
    	
    	return $this->view();
    }
}
