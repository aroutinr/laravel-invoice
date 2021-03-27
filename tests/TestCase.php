<?php

namespace AroutinR\Invoice\Tests;

use AroutinR\Invoice\InvoiceServiceProvider;
use AroutinR\Invoice\Tests\Models\Service;
use AroutinR\Invoice\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public $customer;
    public $invoiceable;

	public function setUp(): void
	{
		parent::setUp();
		
		$this->withFactories(__DIR__.'/Database/factories');

        $this->customer = factory(User::class)->create();
        $this->invoiceable = factory(Service::class)->create();
	}

	protected function getPackageProviders($app)
	{
		return [
			InvoiceServiceProvider::class,
		];
	}

	protected function getEnvironmentSetUp($app)
	{
		include_once __DIR__ . '/Database/migrations/create_users_table.php.stub';
		(new \CreateUsersTable)->up();
		include_once __DIR__ . '/Database/migrations/create_services_table.php.stub';
		(new \CreateServicesTable)->up();
		include_once __DIR__ . '/../database/migrations/create_invoices_table.php.stub';
		(new \CreateInvoicesTable)->up();
		include_once __DIR__ . '/../database/migrations/create_invoice_lines_table.php.stub';
		(new \CreateInvoiceLinesTable)->up();
		include_once __DIR__ . '/../database/migrations/create_invoice_addresses_table.php.stub';
		(new \CreateInvoiceAddressesTable)->up();
		include_once __DIR__ . '/../database/migrations/create_payments_table.php.stub';
		(new \CreatePaymentsTable)->up();
	}
}
