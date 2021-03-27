<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Payment {{ $payment->number }}</title>
	<style>
		@page {
			size: 612pt 396pt;
		}

		.clearfix:after {
		  content: "";
		  display: table;
		  clear: both;
		}

		body {
		  position: relative;
		  max-width: 612pt;
		  height: 340pt; 
		  margin: 0 auto; 
		  background: #FFFFFF; 
		  font-family: Arial, sans-serif; 
		  font-size: 12px; 
		}

		header {
		  padding: 10px 0;
		  border-bottom: 1px solid;
		  margin-bottom: 20px;
		}

		#app-name {
		  float: left;
		  margin-top: 8px;
		  margin-bottom: 10px;
		  width: 350px;
		}

		#app-name p {
		  margin: 0;
		  word-break: normal;
		}

		#app-name a {
		  text-decoration: none;
		  color: #000000;
		}

		#payment-details {
		  text-align: right;
		  margin-top: 8px;
		  margin-bottom: 10px;
		}

		#payment-details p {
		  margin: 0;
		  word-break: normal;
		}

		h1.title {
		  font-size: 1.8em;
		  font-weight: bold;
		  margin: 0px 0px 4px 0px;
		}

		h2.title {
		  font-size: 1.4em;
		  font-weight: normal;
		  margin: 0px 0px 2px 0px;
		}

		h3.title {
		  font-weight: normal;
		  margin: 0px 0px 3px 0px;
		}

		footer {
		  width: 100%;
		  height: 30px;
		  position: absolute;
		  bottom: 0;
		  border-top: 1px solid;
		  padding: 8px 0;
		  text-align: center;
		}

		#print_date {
		  float: left;
		  font-weight: bold;
		}

		#url {
		  float: right;
		}

		#url a {
		  text-decoration: none;
		  color: #000000;
		  font-weight: bold;
		  font-style: italic;
		}

		.text-center {
		  text-align: center;
		}

		.text-right {
		  text-align: right;
		}

		.text-left {
		  text-align: left;
		}

		.text-justify {
		  text-align: justify;
		}
	</style>
</head>
<body>
	<header class="clearfix">
		<div id="app-name">
			<h1 class="title">{{ config('invoice.info.name') }}</h1>
			<p>{{ config('invoice.info.address', '') }}</p>
			<p>{{ config('invoice.info.contact', '') }}</p>
		</div>
		<div id="payment-details">
			<h1 class="title">Payment Number: {{ $payment->number ?? 'N/A' }}</h1>
			<p>Date: <strong>{{ $payment->date }}</strong></p>
			<p>Currency: <strong>{{ $payment->invoice->currency }}</strong></p>
		</div>
	</header>
	<main>
		@isset ($payment->invoice->number)
			<h1>Payment to Invoice # {{ $payment->invoice->number }}</h1>
		@endisset
		@isset ($payment->method)
		    <h2 class="title">Payment method: {{ $payment->method }}</h2>
		@endisset
		@isset ($payment->reference)
		    <h2 class="title">Payment reference: {{ $payment->reference }}</h2>
		@endisset
		<h1>
			Payment Amount: 
			{{ 
				number_format(
					$payment->amount / 100, 
					config('invoice.format.decimals', 2), 
					config('invoice.format.decimal_separator', ','),
					config('invoice.format.thousand_seperator', '.')
				) 
			}}
		</h1>
		<h2 class="title">
			Invoice balance: 
			{{ 
				number_format(
					$payment->invoice->balance / 100, 
					config('invoice.format.decimals', 2), 
					config('invoice.format.decimal_separator', ','),
					config('invoice.format.thousand_seperator', '.')
				) 
			}}
		</h2>
	</main>
	<footer>
		<div id="print_date">Print Date: {{ now() }} / Payment ID: {{ $payment->id }}</div>
		<div id="url">
			<a href="{{ config('invoice.info.url') }}">
				{{ config('invoice.info.url') }}
			</a>
		</div>
	</footer>
</body>
</html>
