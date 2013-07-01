__PayPal SDK for ExpressCheckout and AdaptivePayments.__

[![Build Status](https://travis-ci.org/OpenBuildings/paypal.png?branch=master)](https://travis-ci.org/OpenBuildings/paypal) [![Latest Stable Version](https://poser.pugx.org/openbuildings/paypal/v/stable.png)](https://packagist.org/packages/openbuildings/paypal)

Features:
 - recurring payments
 - simple payments
 - parallel payments
 - chained payments

Installation
------------

You could use this library in your project by running:

    php composer.phar install

[Learn more about Composer](http://getcomposer.org).

Usage
-----

Here is a simple usage example performing a payment with ExpressCheckout:

``` php

// Get a Payment instance using the ExpressCheckout driver
$payment = OpenBuildings\PayPal\Payment::instance('ExpressCheckout');

// Set the order
$payment->order(array(
    'items_price' => 10,
    'shipping_price' => 3,
    'total_price' => 13
));

// Send a SetExpressCheckout API call with return url and cancel url
$response = $payment->set_express_checkout('example.com/success', 'example.com/cancelled');

// Finish the payment with the token and the payer id received.
$payment->do_express_checkout_payment($response['TOKEN'], $response['PAYERID']);

```

Documentation
-------------

 * [Getting started](docs/getting-started.md)
 * [Configuration](docs/configuration.md)
 * [ExpressCheckout](docs/ExpressCheckout.md)
 * [Adaptive Payments](docs/adaptive-payments.md)

Contributing
------------

Read the [Contribution guidelines](CONTRIBUTING.md).

License
-------

Licensed under BSD-3-Clause open-source license.

[License file](LICENSE)