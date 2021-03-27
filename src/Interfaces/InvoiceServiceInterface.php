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
     * Generate invoice referencing Eloquent model.
     *
     * @param Model $customer        Eloquent model.
     * @param Model $invoiceable     Eloquent model.
     * @return InvoiceServiceInterface
     */
    public function __construct(Model $customer, Model $invoiceable);

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
    public function setNumber(string $number): InvoiceService;

    /**
     * Set the currency for the invoice.
     *
     * @return InvoiceService
     */
    public function setCurrency(string $currency): InvoiceService;

    /**
     * Set the date for the invoice.
     *
     * @return InvoiceService
     */
    public function setDate(string $date): InvoiceService;

    /**
     * Add a invoice line type to the invoice.
     *
     * @return InvoiceService
     */
    public function addInvoiceLine(string $description, int $quantity, int $amount): InvoiceService;

    /**
     * Add multiple invoice lines type to the invoice.
     *
     * @return InvoiceService
     */
    public function addInvoiceLines(array $invoice_lines): InvoiceService;

    /**
     * Add a discount line type to the invoice.
     *
     * @return InvoiceService
     */
    public function addFixedDiscountLine(string $description, int $amount): InvoiceService;

    /**
     * Add a discount line type to the invoice.
     *
     * @return InvoiceService
     */
    public function addPercentDiscountLine(string $description, int $amount): InvoiceService;

    /**
     * Add a tax line type to the invoice.
     *
     * @return InvoiceService
     */
    public function addTaxLine(string $description, int $amount): InvoiceService;

    /**
     * Add billing address to the invoice
     * 
     * @return InvoiceService
     */
    public function addBillingAddress(array $billing): InvoiceService;

    /**
     * Add shipping address to the invoice
     * 
     * @return InvoiceService
     */
    public function addShippingAddress(array $shipping): InvoiceService;

    /**
     * Add custom fields to the invoice
     * 
     * @return InvoiceService
     */
    public function addCustomField(string $name, string $value): InvoiceService;

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\View\View
     */
    public function view(array $data = []): View;
}
