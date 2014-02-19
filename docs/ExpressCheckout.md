PayPal Express Checkout provides a very easy and user friendly way for users to make payment. All transactions happen in an overlayer modal box, users will not need to leave your site during the whole process unlike other methods, where users will be redirected to PayPal official site to make payment.

ExpressCheckout does not use any other configurations than those for authentication.

The order is an array of items with three required fields:
 - `name`
 - `price`
 - `quantity`

**Example**:

```php
$items = array();
$items[] = array('name' => 'Item Name #1', 'price' => 3, 'quantity' => 1);
$items[] = array('name' => 'Item Name #2', 'price' => 5, 'quantity' => 3);

$express_checkout = Payment::instance('ExpressCheckout')
  ->order($items)
  ->return_url('example.com/success')
  ->cancel_url('example.com/cancelled');

$response = $express_checkout
  ->set_express_checkout();
  

```

Now you need to redirect user to Paypal page ('www.paypal.com/incontext?token=') using $response['TOKEN']

Upon success you need to finaly confirm the payment:
```php
$params['TOKEN']   = $_REQUEST['token'];
$params['PAYERID'] = $_REQUEST['PayerID'];

$express_checkout = Payment::instance('ExpressCheckout')
    ->order($items); //the same $items as before

$express_checkout
  ->do_express_checkout_payment($params['TOKEN'], $params['PAYERID']);
```

**Notes**:

You should store the token or the whole response in a permanent or a session storage. They will be needed after the user has confirmed the payment on paypal.com.