<?php

namespace AroutinR\Invoice;

use AroutinR\Invoice\Interfaces\InvoiceServiceInterface;
use AroutinR\Invoice\Services\InvoiceService;
use Illuminate\Support\ServiceProvider;

class InvoiceServiceProvider extends ServiceProvider
{
	public function register()
	{
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'invoice');
	}

	public function boot()
	{
        $this->app->bind(InvoiceServiceInterface::class, function ($app) {
            return new InvoiceService();
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('invoice.php'),
            ], 'config');

            if (! class_exists('CreateInvoicesTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_invoices_table.php.stub' => database_path('migrations/2021_03_24_173605_create_invoices_table.php'),
                ], 'migrations');
            }

            if (! class_exists('CreateInvoiceLinesTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_invoice_lines_table.php.stub' => database_path('migrations/2021_03_24_173610_create_invoice_lines_table.php'),
                ], 'migrations');
            }

            if (! class_exists('CreateInvoiceAddressesTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_invoice_addresses_table.php.stub' => database_path('migrations/2021_03_24_173615_create_invoice_addresses_table.php'),
                ], 'migrations');
            }
        }
	}
}
