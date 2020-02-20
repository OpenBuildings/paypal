<?php

use OpenBuildings\PayPal\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_ExpressCheckoutTest extends TestCase {

	public function setUp()
	{
		parent::setUp();

		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::get_express_checkout_details
	 */
	public function test_get_express_checkout_details_require_token()
	{
		$payment = Payment::instance('ExpressCheckout');

		$this->expectException('OpenBuildings\PayPal\Exception');
		$this->expectExceptionMessage('You must provide a TOKEN parameter for method "OpenBuildings\PayPal\Payment_ExpressCheckout::get_express_checkout_details"');

		$payment->get_express_checkout_details(array());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::get_express_checkout_details
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::_request
	 */
	public function test_get_express_checkout_details_request()
	{
		$mock_payment = $this->getExpressCheckoutMock();

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

	/**
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::set_express_checkout
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::_set_params
	 */
	public function test_set_express_checkout()
	{
		$mock_payment = $this->getExpressCheckoutMock();

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

	/**
	 * @covers OpenBuildings\PayPal\Payment_ExpressCheckout::do_express_checkout_payment
	 */
	public function test_do_express_checkout_payment()
	{
		$mock_payment = $this->getExpressCheckoutMock();

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

	private function getExpressCheckoutMock(): MockObject
    {
        return $this->getMockBuilder('OpenBuildings\PayPal\Payment_ExpressCheckout')
            ->disableOriginalConstructor()
            ->setMethods(['request'])
            ->getMock();
    }
}