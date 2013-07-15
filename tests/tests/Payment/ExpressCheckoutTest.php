<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_ExpressCheckoutTest extends \PHPUnit_Framework_TestCase {

	public function test_get_express_checkout_details_require_token()
	{
		$payment = Payment::instance('ExpressCheckout');

		$this->setExpectedException('OpenBuildings\PayPal\Exception', 'You must provide a TOKEN parameter for method "OpenBuildings\PayPal\Payment_ExpressCheckout::get_express_checkout_details"');

		$payment->get_express_checkout_details(array());
	}

	public function test_get_express_checkout_details_request()
	{
		$mock_payment = $this->getMockBuilder('Payment_ExpressCheckout')
			->setMethods(NULL)
			->getMock();
		
		// set $callOriginalMethods to TRUE for the mock
		$mock_payment = $this->getMock('Payment_ExpressCheckout', NULL, array(), '', TRUE, TRUE, TRUE, FALSE, TRUE);

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
}