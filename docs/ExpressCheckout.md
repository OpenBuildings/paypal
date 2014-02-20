PayPal Express Checkout provides a very easy and user friendly way for users to make payment. All transactions happen in an overlayer modal box or popup window (it mostly depends on the user choice of "Remember Me Cookie"). It seems like users don't need to leave your site during the whole process unlike other methods, where users will be redirected to the 'full' PayPal official site to make payment.

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

Now when you have ```$response['TOKEN']``` you need to open a popup with Paypal page. You can get valid URL for the form action by ```$express_checkout->ec_form_action_url($response['TOKEN'])```:
```html
<form action="https://www.paypal.com/incontext?token=TOKEN" method="POST">
    <input type='image' name='paypal_submit' id='paypal_submit'
     src='https://www.paypal.com/en_US/i/btn/btn_dg_pay_w_paypal.gif' border='0' align='top' alt='Pay with PayPal'/>
</form>
```
Upon success Paypal redirects to your ```return_url``` where you need to finally complete the payment and act accordingly:
```php
$params['TOKEN']   = $_REQUEST['token'];
$params['PAYERID'] = $_REQUEST['PayerID'];

$express_checkout = Payment::instance('ExpressCheckout')
    ->order($items); //the same $items as before

//In $response you'd have some extra important information like transaction id or fees taken by Paypal
$response = $express_checkout
  ->do_express_checkout_payment($params['TOKEN'], $params['PAYERID']);

$ack = strtoupper($response["ACK"]);
if("SUCCESS" == $ack || "SUCCESSWITHWARNING" == $ack)
{
    /*
    * TODO: Proceed with desired action after the payment
    * (ex: start download, start streaming, add coins to the game, etc.)
    */
}
```

**Notes**:

 - You should store the token or the whole response in a permanent or a session storage. They will be needed after the user has confirmed the payment on paypal.com.

 - You also need to have this code before ```</body>``` on your website with the Paypal button. A good choice for ```expType``` is ```instant``` but for more options you may refer to [Paypal documentation](https://developer.paypal.com/docs/classic/express-checkout/digital-goods/IntroducingExpressCheckoutDG/).
```html
    <script src='https://www.paypalobjects.com/js/external/dg.js' type='text/javascript'></script>
    <script>
        var dg = new PAYPAL.apps.DGFlow({
            trigger: 'paypal_submit', // ID of the form submit button
		    expType: 'instant'
	        });
    </script>
```

 - Don't forget to close the popup window, you may use a code like this:
```javascript
window.onload = function(){
    if(window.opener){
         window.close();
     }
    else{
         if(top.dg.isOpen() == true){
             top.dg.closeFlow();
             return true;
          }
      }
};
```
