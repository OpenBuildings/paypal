<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_ExpressCheckoutTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		parent::setUp();

		Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
	}

	public function test_get_express_checkout_details_require_token()
	{
		$payment = Payment::instance('ExpressCheckout');

		$this->setExpectedException('OpenBuildings\PayPal\Exception', 'You must provide a TOKEN parameter for method "OpenBuildings\PayPal\Payment_ExpressCheckout::get_express_checkout_details"');

		$payment->get_express_checkout_details(array());
	}

	public function test_get_express_checkout_details_request()
	{
		$mock_payment = $this->getMock('OpenBuildings\PayPal\Payment_ExpressCheckout', array('request'));

		$mock_payment
			->expects($this->once())
			->method('request')
			->with($this->identicalTo('https://api-3t.sandbox.paypal.com/nvp', $this->identicalTo(array(
				'METHOD' => 'GetExpressCheckoutDetails',
				'VERSION' => '98.0',
				'USER' => '',
				'PWD' => '',
				'SIGNATURE' => '',
				'TOKEN' => 'ABCDE',
				'param' => 'value'
			))));

		$mock_payment->get_express_checkout_details(array(
			'TOKEN' => 'ABCDE',
			'param' => 'value'
		));
	}

	public function test_set_express_checkout()
	{
		$mock_payment = $this->getMock('OpenBuildings\PayPal\Payment_ExpressCheckout', array('request'));

		$mock_payment
			->expects($this->once())
			->method('request')
			->with($this->identicalTo('https://api-3t.sandbox.paypal.com/nvp', $this->identicalTo(array(
				'METHOD' => 'SetExpressCheckout',
				'VERSION' => '98.0',
				'USER' => '',
				'PWD' => '',
				'SIGNATURE' => '',
				'PAYMENTREQUEST_0_AMT' => '10.00',
				'PAYMENTREQUEST_0_ITEMAMT' => '7.00',
				'PAYMENTREQUEST_0_SHIPPINGAMT' => '3.00',
				'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
				'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
				'RETURNURL' => 'example.com/success',
				'CANCELURL' => 'example.com/cancelled',
				'useraction' => 'commit',
				'NOSHIPPING' => 1,
				'ADDROVERRIDE' => 0,
				'PAYMENTREQUEST_0_NOTIFYURL' => 'example.com/ipn'
			))));

		$mock_payment
			->order(array(
				'total_price' => 10,
				'items_price' => 7,
				'shipping_price' => 3,
			))
			->config('currency', 'EUR')
			->return_url('example.com/success')
			->cancel_url('example.com/cancelled')
			->notify_url('example.com/ipn');

		$mock_payment->set_express_checkout();
	}

	public function test_do_express_checkout_payment()
	{
		$mock_payment = $this->getMock('OpenBuildings\PayPal\Payment_ExpressCheckout', array('request'));

		$mock_payment
			->expects($this->once())
			->method('request')
			->with($this->identicalTo('https://api-3t.sandbox.paypal.com/nvp', $this->identicalTo(array(
				'TOKEN' => 'ABCDE',
				'PAYERID' => 'PAYERXYZ',
				'METHOD' => 'DoExpressCheckoutPayment',
				'VERSION' => '98.0',
				'USER' => '',
				'PWD' => '',
				'SIGNATURE' => '',
				'PAYMENTREQUEST_0_AMT' => '10.00',
				'PAYMENTREQUEST_0_ITEMAMT' => '7.00',
				'PAYMENTREQUEST_0_SHIPPINGAMT' => '3.00',
				'PAYMENTREQUEST_0_CURRENCYCODE' => 'EUR',
				'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
				'RETURNURL' => 'example.com/success',
				'CANCELURL' => 'example.com/cancelled',
				'useraction' => 'commit',
				'NOSHIPPING' => 1,
				'ADDROVERRIDE' => 0,
				'PAYMENTREQUEST_0_NOTIFYURL' => 'example.com/ipn'
			))));

		$mock_payment
			->order(array(
				'total_price' => 10,
				'items_price' => 7,
				'shipping_price' => 3,
			))
			->config('currency', 'EUR');

		$mock_payment->do_express_checkout_payment('ABCDE', 'PAYERXYZ');
	}
}