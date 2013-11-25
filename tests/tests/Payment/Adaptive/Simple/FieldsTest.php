<?php

use OpenBuildings\PayPal\Payment;
use OpenBuildings\PayPal\Payment_Adaptive_Simple;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Simple_FieldsTest extends \PHPUnit_Framework_TestCase {

	public $payment;

	public function setUp()
	{
		parent::setUp();

		$this->payment = Payment::instance('Adaptive_Simple');
		$this->payment->order(array(
			'receiver' => array(
				'email' => 'contact@example.com',
				'amount' => 10
			)
		));
		$this->payment
			->config('fees_payer', Payment_Adaptive_Simple::FEES_PAYER_EACHRECEIVER)
			->config('email', 'sender@example.com');
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_sender_email()
	{
		$this->payment->implicit_approval(TRUE);

		$this->assertArrayHasKey('senderEmail', $this->payment->fields());

		$this->payment->implicit_approval(FALSE);

		$this->assertArrayNotHasKey('senderEmail', $this->payment->fields());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_sender_account_id()
	{
		$this->markTestIncomplete();
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_validate_fees_payer()
	{
		$this->payment->config('fees_payer', 'invalid_fees_payer');

		$this->setExpectedException('OpenBuildings\PayPal\Exception', 'Fees payer type "invalid_fees_payer" is not allowed!');

		$this->payment->fields();
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_ipn_url()
	{
		$this->payment->notify_url('example.com/ipn');

		$fields = $this->payment->fields();

		$this->assertArrayHasKey('ipnNotificationUrl', $fields);
		$this->assertEquals('example.com/ipn', $fields['ipnNotificationUrl']);
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_tracking_id()
	{
		$this->payment->order(array(
			'receiver' => array(
				'email' => 'contact@example.com',
				'amount' => 10
			),
			'order_number' => 'ABCDE'
		));

		$fields = $this->payment->fields();

		$this->assertArrayHasKey('trackingId', $fields);
		$this->assertEquals('ABCDE', $fields['trackingId']);
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 */
	public function test_reverse_on_error()
	{
		$this->markTestIncomplete();
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::fields
	 * @covers OpenBuildings\PayPal\Payment_Adaptive_Simple::_set_payment_type
	 */
	public function test_set_payment_type()
	{
		$this->markTestIncomplete();
	}
}
