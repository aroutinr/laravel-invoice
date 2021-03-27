<?php
namespace AroutinR\Invoice\Interfaces;

use AroutinR\Invoice\Models\Invoice;
use AroutinR\Invoice\Models\Payment;
use AroutinR\Invoice\Services\PaymentService;
use Illuminate\Contracts\View\View;

interface PaymentServiceInterface
{
    /**
     * Generate payment referencing Eloquent model.
     *
     * @param Invoice $invoice Eloquent model.
     * @return PaymentServiceInterface
     */
    public function __construct(Invoice $invoice);

    /**
     * Set the date for the payment.
     *
     * @return PaymentService
     */
    public function setDate(string $date): PaymentService;

    /**
     * Set the amount for the payment.
     *
     * @return PaymentService
     */
    public function setAmount(int $amout): PaymentService;

    /**
     * Set the number for the payment.
     *
     * @return PaymentService
     */
    public function setNumber(string $number): PaymentService;

    /**
     * Set the method for the payment.
     *
     * @return PaymentService
     */
    public function setMethod(string $method): PaymentService;

    /**
     * Set the reference for the payment.
     *
     * @return PaymentService
     */
    public function setReference(string $reference): PaymentService;

    /**
     * Save the payment to the database.
     *
     * @return Payment
     */
    public function save(): Payment;

    /**
     * Get the View instance for the payment.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function view(array $data = []): View;
}
