<?php

use OpenBuildings\PayPal\Payment;
use OpenBuildings\PayPal\Payment_Adaptive;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_AdaptiveTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::webapps_url
	 */
	public function test_webapps_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay', Payment_Adaptive::webapps_url());

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://www.paypal.com/webapps/adaptivepayment/flow/pay?param1=value1&param2=value2', Payment_Adaptive::webapps_url(array(
			'param1' => 'value1',
			'param2' => 'value2'
		)));

		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?param1=value1&param2=value2&expType=mini', Payment_Adaptive::webapps_url(array(
			'param1' => 'value1',
			'param2' => 'value2'
		), TRUE));
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::approve_url
	 */
	public function test_approve_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?paykey=some_pay_key&expType=mini', Payment_Adaptive::approve_url('some_pay_key', TRUE));

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=some_pay_key', Payment_Adaptive::approve_url('some_pay_key'));
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::ap_api_url
	 */
	public function test_ap_api_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://svcs.sandbox.paypal.com/AdaptivePayments', Payment_Adaptive::ap_api_url());

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://svcs.paypal.com/AdaptivePayments/ExecutePayment', Payment_Adaptive::ap_api_url('ExecutePayment'));
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::common_fields
	 */
	public function test_common_fields()
	{
		$this->assertSame(array(
			'requestEnvelope.errorLanguage' => 'en_US',
			'requestEnvelope.detailLevel' => 'ReturnAll',
		), Payment::instance('Adaptive')->common_fields());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::implicit_approval
	 */
	public function test_implicit_approval()
	{
		$payment = Payment::instance('Adaptive');
		$this->assertFalse($payment->implicit_approval());

		$payment->implicit_approval(TRUE);
		$this->assertTRUE($payment->implicit_approval());

		$payment->implicit_approval(FALSE);
		$this->assertFalse($payment->implicit_approval());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::action_type
	 */
	public function test_action_type()
	{
		$payment = Payment::instance('Adaptive');
		$this->assertEquals('PAY', $payment->action_type());

		$payment->action_type('CREATE');
		$this->assertEquals('CREATE', $payment->action_type());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::do_payment
	 */
	public function test_do_payment()
	{
		
	}
}