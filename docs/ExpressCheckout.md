ExpressCheckout does not use any other configurations than those for authentication.

The order has three required fields:
 - `total_price`
 - `items_price`
 - `shipping_price`

**Example**:

``` php
$express_checkout = Payment::instance('ExpressCheckout')
  ->order(array(
    'items_price' => 50,
    'shipping_price' => 10,
    'total_price' => 60
  ))
  ->return_url('example.com/success')
  ->cancel_url('example.com/cancelled')
  ->notify_url('example.com/ipn');

$response = $express_checkout
  ->set_express_checkout();
  
// Redirecting the user to confirm the payment using $response['TOKEN']

$express_checkout
  ->do_express_checkout_payment($response['TOKEN'], $response['PAYERID']);
```

**Notes**:

You should store the token or the whole response in a permanent or a session storage. They will be needed after the user has confirmed the payment on paypal.com.
