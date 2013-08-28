Available payments:
-------------------

 - ExpressCheckout
 - Recurring
 - Adaptive (a.k.a. simple payments)
 - Adaptive_Parallel (a.k.a. parallel payments)
 - Adaptive_Chained (a.k.a. chained payments)


Common methods:
--------------------

 - `config()` - Get or set the configuration. See [Configuration docs](configuration.md)
 - `order()` - Get or set the order. Consist of `items_price`, `shipping_price` and `total_price`.
    Some payment types may require/allow additional parameters of the order (e.g. Adaptive payments could have a receiver list).
 - `return_url()` - Get or set the return URL.
 - `cancel_url()` - Get or set the cancel URL.
 - `notify_url()` - Get or set the notify URL (a.k.a. IPN URL).

---

**Example**:

``` php
Payment::instance('Adaptive_Chained')
  ->config('fees_payer', Payment_Adaptive::FEES_PAYER_EACHRECEIVER)
  ->config('currency', 'GBP')
  ->config('email', 'primary@example.com')
  ->order(array(
    'total_price' => '10.00',
    'receivers' => array(
      array(
        'email' => 'primary@example.com',
        'amount' => '10.00',
      ),
      array(
        'email' => 'secondary@example.com',
        'amount' => '3.00',
      ),
    )
  ))
  ->return_url('example.com/success')
  ->cancel_url('example.com/cancelled')
  ->implicit_approval(TRUE)
  ->do_payment();
```
