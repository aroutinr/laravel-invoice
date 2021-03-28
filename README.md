# Laravel Invoice

[![Latest Stable Version](https://poser.pugx.org/aroutinr/laravel-invoice/v)](//packagist.org/packages/aroutinr/laravel-invoice) [![License](https://poser.pugx.org/aroutinr/laravel-invoice/license)](//packagist.org/packages/aroutinr/laravel-invoice) [![Build Status](https://travis-ci.org/aroutinr/laravel-invoice.svg?branch=master)](https://travis-ci.org/aroutinr/laravel-invoice)

Laraval package that allows you to create invoices, manage payments and track them from your models!

```php
<?php

use AroutinR\Inovoice\Facades\CreateInvoice;

return CreateInvoice::for($customer, $invoiceable)
	->billingAddress([
		'name' => 'Billing Name',
		'line_1' => 'Billing Address Line 1',
		'line_2' => 'Billing Address Line 2',
		'line_3' => 'Billing Address Line 3',
	])
	->shippingAddress([
		'name' => 'Shipping Name',
		'line_1' => 'Shipping Address Line 1',
		'line_2' => 'Shipping Address Line 2',
		'line_3' => 'Shipping Address Line 3',
	])
	->invoiceNumber('00-112233')
	->invoiceLine('White T-Shirt', 3, 3999)
	->invoiceLine('Running Shoes', 1, 7999)
	->invoiceLine('Another cool product', 1, 9999)
	->fixedDiscountLine('A Cool Discount', 1000)
	->taxLine('Tax 3%', 300)
	->customField('Origin', 'Houston')
	->customField('Destination', 'Miami')
	->customField('Carrier', 'UPS')
	->saveAndView();
```

This will return a view with a simple and fully customizable invoice format.

![Laravel Invoice Screenshot](/resources/images/invoice_example.png)

## Requirements

- PHP >=7.3
- Laravel 6 | 7 | 8

## Install

Install through Composer.

```bash
$ composer require aroutinr/laravel-invoice
```

Publish the migration.

``` bash
$ php artisan vendor:publish --provider="AroutinR\Invoice\InvoiceServiceProvider" --tag="migrations"
```

Then run the migrations.

``` bash
$ php artisan migrate
```

Additionally, you can publish the `invoice.php` configuration file. From here you can define various options for the operation of the package. Feel free to change whatever you need, such as the default currency, invoice header name, address and contact information, and more.

``` bash
$ php artisan vendor:publish --provider="AroutinR\Invoice\InvoiceServiceProvider" --tag="config"
```

If you want to change the appearance of the invoice or the payment receipt, you can publish the blade views:

``` bash
$ php artisan vendor:publish --provider="AroutinR\Invoice\InvoiceServiceProvider" --tag="views"
```

They will be saved in `<project_root>/resources/views/vendor/laravel-invoice/`

## Usage

__Important: the amount is expressed in cents!__

The package has traits to keep track of the invoices that are issued. The first one is __CustomerHasInvoice__ this trait must be added in the User or Company model for example, this will define the Customer associated with the invoice. In this same model you can add the __HasAddresses__ trait, it will allow you to keep track of the addresses that you add to your invoices with the Customer. The second trait is __HasInvoice__, this must be added in the models that will be invoiceables, for example a service provided to the customer, subscription, orders, etc.

``` php

// 'app/User.php' or 'app/Models/User.php' 

namespace App;

use AroutinR\Invoice\Traits\CustomerHasInvoice;
use AroutinR\Invoice\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use CustomerHasInvoice, HasAddresses; 

    // This traits enable the following Eloquent relationship:
    // 
    // CustomerHasInvoice:
    // ->invoices() This will fetch all the invoices related with the model
    // 
    // HasAddresses
    // ->addresses() This will fetch all the addresses related with the model
    // ->billingAddress() This will fetch the billing addresses related with the model
    // ->shippingAddress() This will fetch the shipping addresses related with the model
}

// 'app/Order.php' or 'app/Models/Order.php' 

namespace App;

use AroutinR\Invoice\Traits\HasInvoice;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasInvoice; 

    // This traits enable the following Eloquent relationship:
    // 
    // ->invoices() This will fetch all the invoices related with the model
}
```

To create a new invoice, you only have to obtain a Customer model, an Invoiceable model and use the CreateInvoice Facade.

``` php

// 'app/Http/Controllers/HomeController'

namespace App\Http\Controllers;

use App\User;
use App\Order;
use AroutinR\Invoice\Facade\CreateInvoice;

class HomeController extends Controller
{
    public function index()
    {
    	$customer = User::find(1); // Grab a Customer model
    	$invoiceable = Order::find(1); // Grab a Invoiceable model

    	$invoice = CreateInvoice::for($customer, $invoiceable)

    		// Use the billingAddress() and shippingAddress() methods to add this information to the invoice.
    		// These methods accept an array in the following format:
			->billingAddress([
				'name' => 'Billing Name',
				'line_1' => 'Billing Line 1',
				'line_2' => 'Billing Line 2',
				'line_3' => 'Billing Line 3',
			]) // Optional
			->shippingAddress([
				'name' => 'Shipping Name',
				'line_1' => 'Shipping Line 1',
				'line_2' => 'Shipping Line 2',
				'line_3' => 'Shipping Line 3',
			]) // Optional

    		// invoiceLine() needs trhee arguments.
    		// The first argument you need a description for the line.
    		// The second argument is the quantity (for example 2, for 2 T-shirts)
    		// And the third argument is the Unit price.
    		// You can add as many lines as you need
			->invoiceLine('Some description', 1, 10000) // Required

			// Also, you can use the invoceLines() method (note the "s" at the end). 
			// This method accepts an array to enter multiple lines at the same time.
			->invoiceLines([
				[
					'quantity' => 1, 
					'amount' => 10000,
					'description' => 'Some description',
				],
				[
					'quantity' => 1, 
					'amount' => 20000,
					'description' => 'Another description'
				],
				[
					'quantity' => 2, 
					'amount' => 30000,
					'description' => 'Final description'
				]
			]) // Optional if invoiceLine() is used

			// You can add a fixed discount to the invoice with the fixedDiscountLine() method
			// This method needs a description as firt argument and the discount amount as the second argument
			// You can also add a percentage-based discount by using the percentDiscountLine() method.
			// This method use the same arguments as the fixedDiscountLine()
			->fixedDiscountLine('A Cool Discout', 5000) // Optional

			// To add a percentage value for the tax, use the taxLine() method. 
			// This method requires a description and the value of the tax.
			->taxLine('Tax 3%', 300) // Optional

			// Use the customField() method to add any additional information to the invoice, 
			// such as payment terms, warranty information, and so on. 
			// This method takes two arguments, the custom field name and the description. 
			// By default you can enter up to 4 custom fields, this can be changed in config/invoice.php file
			->customField('Invoice Terms', 'Due on receipt') // Optional
			->customField('Origin', 'Houston') // Optional
			->customField('Destination', 'Miami') // Optional
			->customField('Carrier', 'UPS') // Optional

			// Finally, use the save() method to create the invoice and store it in the database 
			// or saveAndView() to store and return the invoice format for printing.
			->save(); // or ->saveAndView();
    }
}
```

To create a payment to an invoice, you only need to use the Facade CreatePayment and pass in a invoice.

``` php

// 'app/Http/Controllers/HomeController'

namespace App\Http\Controllers;

use AroutinR\Invoice\Facade\CreatePayment;

class HomeController extends Controller
{
    public function index()
    {
    	$invoice = AroutinR\Invoice\Models\Invoice::find(1)

    	$payment = CreatePayment::for($invoice)

    		// Use the method paymentAmount() to especify the amount of the payment
    		// It can be a partial or full payment of the invoice.
			->paymentAmount(10000) // Required

			// This method is used to specify the number of the payment receipt.
			->paymentNumber('PAYMENT-123') // Optional

			// Define the payment method for this payment.
			// The config file has an array where you can specify the different methods you need
			// and then you can use this configuration variable in your blade forms where you will 
			// enter the payment information, for excample:
			// @foreach (config('invoice.payment_methods') as $method)
			->paymentMethod('Check') // Optional

			// Use this method to specify the Payment reference or some relevant information
			->paymentReference('Check # 001122') // Optional
			
			// Finally, use the save() method to create the invoice and store it in the database 
			// or saveAndView() to store and return the invoice format for printing.
			->save(); // or ->saveAndView();
    }
}
```

This is the example of the payment receipt.

![Laravel Invoice Screenshot](/resources/images/payment_example.png)

In the following list you will find all the methods available in each Facade.

__AroutinR\Invoice\Facades\CreateInvoice__

| Method | Description |
| --- | --- |
| for() | Required. Arguments: Customer model, Invoiceable model |
| invoiceLine() | Required. Arguments: (string) description, (int) quantity, (int) amount |
| invoiceCurrency() | Optional. Arguments: (string) currency code |
| invoiceNumber() | Optional. Arguments: (string) invoice number |
| invoiceDate() | Optional. Arguments: (string) invoice date |
| fixedDiscountLine() | Optional. Arguments: (string) description, (int) amount |
| percentDiscountLine() | Optional. Arguments: (string) description, (int) amount |
| taxLine() | Optional. Arguments: (string) description, (int) amount |
| billingAddress() | Optional. Arguments: (array) Format: ['name' => 'Name', 'line_1' => 'Line 1, 'line_2' => 'Line 2, 'line_3' => 'Line 3'] |
| shippingAddress() | Optional. Arguments: (array) Format: ['name' => 'Name', 'line_1' => 'Line 1, 'line_2' => 'Line 2, 'line_3' => 'Line 3'] |
| customField() | Optional. Arguments: (string) name, (string) description |
| save() | Required. Save the invoice in the database |
| saveAndView() | Required if save is not present. Save the invoice in the database and render a view for the invoice |

__AroutinR\Invoice\Facades\CreatePayment__

| Method | Description |
| --- | --- |
| for() | Required. Arguments: Invoice model |
| paymentAmount() | Required. Arguments: (int) amount |
| paymentNumber() | Optional. Arguments: (string) invoice number |
| paymentDate() | Optional. Arguments: (string) invoice date |
| paymentMethod() | Optional. Arguments: (string) description |
| paymentReference() | Optional. Arguments: (string) description |
| save() | Required. Save the payment in the database |
| saveAndView() | Required if save is not present. Save the payment in the database and render a view for the payment |

## Contributing

Feel free to contribute to this project. Any correction or improvement is welcome.

## Testing

Run tests with PHPUnit:

```bash
vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email me r.aroutin@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
