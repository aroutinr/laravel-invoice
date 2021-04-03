# Changelog

All notable changes to `laravel-invoice` will be documented in this file.

##  1.0.12 - 2021-04-02
- Method calculateInvoiceAmount public

##  1.0.11 - 2021-04-02
- Add update() method for update a invoice

##  1.0.10 - 2021-04-02
- Change custom fields array format 

##  1.0.9 - 2021-03-31
- Added Due date to invoices

##  1.0.8 - 2021-03-29
- fix quantity number format

##  1.0.7 - 2021-03-29
- Added support for decimals numbers on invoiceLine() quantity

##  1.0.6 - 2021-03-29
- Fix default valies for currency and date after reset

##  1.0.5 - 2021-03-29
- Reset InvoiceService properties after save

##  1.0.4 - 2021-03-29
- Changes on InvoiceService
- Add invoice notes

##  1.0.3 - 2021-03-28
- Minimun requirements to add billing and invoice address

##  1.0.2 - 2021-03-28
- Fix percent Discount and Tax operation

##  1.0.1 - 2021-03-28
- Added Support for Laravel 6 migrations
- Added `onDelete('cascade')` for invoices related tables

##  1.0.0
- Initial release
