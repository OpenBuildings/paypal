Global configuration
--------------------

`Payment::$config` is a static variable which is merged with other configurations.
It is useful for setting authentication credentials or a default currency.

Credentials consist of the following:

 - `app_id`
 - `username`
 - `password`
 - `signature`
 - `email`

Different PayPal APIs use different authentication mechanisms. Consult with the PayPal developer documentation which to use.

In order to run in a sandbox mode use:
```php
Payment::environment(Payment::ENVIRONMENT_SANDBOX);
```

Instance configuration
----------------------

Instances hold their own configuration which could be changed regardless of the global config.

You can set a configuration on an instance via the `config` method:

- **associative array**

``` php
Payment::instance('Adaptive_Simple')
  ->config(array(
    'currency' => 'USD',
    'fees_payer' => Payment_Adaptive::FEES_PAYER_SENDER
  ));
```

or 

- **setter**

``` php
Payment::instance('Adaptive_Simple')
  ->config('payment_type', Payment_Adaptive::PAYMENT_TYPE_GOODS);
```

---

Available configuration options could be seen in the guides for the different PayPal APIs - ExpressCheckout or AdaptivePayments,
