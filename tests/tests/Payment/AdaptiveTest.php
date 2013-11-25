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
		), Payment::instance('Adaptive_Simple')->common_fields());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::parse_response
	 */
	public function test_parse_response()
	{
		$this->markTestIncomplete();
	}
}
