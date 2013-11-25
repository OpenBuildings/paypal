<?php

use OpenBuildings\PayPal\Payment;
use OpenBuildings\PayPal\Payment_Adaptive_Simple;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_SimpleTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::approve_url
	 */
	public function test_approve_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?paykey=some_pay_key&expType=mini', Payment_Adaptive_Simple::approve_url('some_pay_key', TRUE));

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=some_pay_key', Payment_Adaptive_Simple::approve_url('some_pay_key'));
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::implicit_approval
	 */
	public function test_implicit_approval()
	{
		$payment = Payment::instance('Adaptive_Simple');
		$this->assertFalse($payment->implicit_approval());

		$payment->implicit_approval(TRUE);
		$this->assertTRUE($payment->implicit_approval());

		$payment->implicit_approval(FALSE);
		$this->assertFalse($payment->implicit_approval());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::action_type
	 */
	public function test_action_type()
	{
		$payment = Payment::instance('Adaptive_Simple');
		$this->assertEquals('PAY', $payment->action_type());

		$payment->action_type('CREATE');
		$this->assertEquals('CREATE', $payment->action_type());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::do_payment
	 */
	public function test_do_payment()
	{
		$this->markTestIncomplete();
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::pay
	 */
	public function test_pay()
	{
		$this->markTestIncomplete();
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::execute_payment
	 */
	public function test_execute_payment()
	{
		$this->markTestIncomplete();
	}
}
