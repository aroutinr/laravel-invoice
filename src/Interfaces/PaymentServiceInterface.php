<?php
namespace AroutinR\Invoice\Interfaces;

use AroutinR\Invoice\Models\Invoice;
use AroutinR\Invoice\Models\Payment;
use AroutinR\Invoice\Services\PaymentService;
use Illuminate\Contracts\View\View;

interface PaymentServiceInterface
{
    /**
     * Construct the PaymentService class
     *
     * @return void
     */
    public function __construct();

    /**
     * Setup the PaymentService with the Eloquent models.
     *
     * @param Invoice $invoice Eloquent model.
     * @return PaymentService
     */
    public function for(Invoice $invoice): PaymentService;

    /**
     * Set the date for the payment.
     *
     * @return PaymentService
     */
    public function paymentDate(string $date): PaymentService;

    /**
     * Set the amount for the payment.
     *
     * @return PaymentService
     */
    public function paymentAmount(int $amout): PaymentService;

    /**
     * Set the number for the payment.
     *
     * @return PaymentService
     */
    public function paymentNumber(string $number): PaymentService;

    /**
     * Set the method for the payment.
     *
     * @return PaymentService
     */
    public function paymentMethod(string $method): PaymentService;

    /**
     * Set the reference for the payment.
     *
     * @return PaymentService
     */
    public function paymentReference(string $reference): PaymentService;

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

    /**
     * Save the payment to the database and get the View instance for the payment.
     *
     * @return \Illuminate\View\View
     */
    public function saveAndView(): View;
}
