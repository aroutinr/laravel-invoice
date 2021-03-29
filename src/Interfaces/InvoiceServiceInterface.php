<?php
namespace AroutinR\Invoice\Interfaces;

use AroutinR\Invoice\Services\InvoiceService;
use AroutinR\Invoice\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface InvoiceServiceInterface
{
    /**
     * Construct the InvoiceService class
     *
     * @return void
     */
    public function __construct();

    /**
     * Setup the InvoiceService with the Eloquent models.
     *
     * @param Model $customer        Eloquent model.
     * @param Model $invoiceable     Eloquent model.
     * @return InvoiceServiceInterface
     */
    public function for(Model $customer, Model $invoiceable): InvoiceService;

    /**
     * Save the invoice to the database.
     *
     * @return Invoice
     */
    public function save(): Invoice;

    /**
     * Set the number for the invoice.
     *
     * @return InvoiceService
     */
    public function invoiceNumber(string $number): InvoiceService;

    /**
     * Set the currency for the invoice.
     *
     * @return InvoiceService
     */
    public function invoiceCurrency(string $currency): InvoiceService;

    /**
     * Set the date for the invoice.
     *
     * @return InvoiceService
     */
    public function invoiceDate(string $date): InvoiceService;

    /**
     * Add a invoice line type to the invoice.
     *
     * @return InvoiceService
     */
    public function invoiceLine(string $description, int $quantity, int $amount): InvoiceService;

    /**
     * Add multiple invoice lines type to the invoice.
     *
     * @return InvoiceService
     */
    public function invoiceLines(array $invoice_lines): InvoiceService;

    /**
     * Add a discount line type to the invoice.
     *
     * @return InvoiceService
     */
    public function fixedDiscountLine(string $description, int $amount): InvoiceService;

    /**
     * Add a discount line type to the invoice.
     *
     * @return InvoiceService
     */
    public function percentDiscountLine(string $description, int $amount): InvoiceService;

    /**
     * Add a tax line type to the invoice.
     *
     * @return InvoiceService
     */
    public function taxLine(string $description, int $amount): InvoiceService;

    /**
     * Add billing address to the invoice
     * 
     * @return InvoiceService
     */
    public function billingAddress(array $billing): InvoiceService;

    /**
     * Add shipping address to the invoice
     * 
     * @return InvoiceService
     */
    public function shippingAddress(array $shipping): InvoiceService;

    /**
     * Add custom fields to the invoice
     * 
     * @return InvoiceService
     */
    public function customField(string $name, string $value): InvoiceService;

    /**
     * Add notes to the invoice
     * 
     * @return InvoiceService
     */
    public function addNote(string $note): InvoiceService;

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function view(Invoice $invoice, array $data = []): View;

    /**
     * Save the invoice to the database and get the View instance for the invoice.
     *
     * @return \Illuminate\View\View
     */
    public function saveAndView(): View;
}
