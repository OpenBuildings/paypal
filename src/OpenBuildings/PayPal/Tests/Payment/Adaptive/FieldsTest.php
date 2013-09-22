<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_FieldsTest extends \PHPUnit_Framework_TestCase {

	public $payment;

	public function setUp()
	{
		parent::setUp();

		$this->payment = Payment::instance('Adaptive');
		$this->payment->order(array(
			'receiver' => array(
				'email' => 'contact@example.com',
				'amount' => 10
			)
		));
		$this->payment->config('fees_payer', Payment_Adaptive::FEES_PAYER_EACHRECEIVER);
	}

	public function test_sender_email()
	{
		$this->payment->implicit_approval(TRUE);

		$this->assertArrayHasKey('senderEmail', $this->payment->fields());

		$this->payment->implicit_approval(FALSE);

		$this->assertArrayNotHasKey('senderEmail', $this->payment->fields());
	}

	public function test_validate_fees_payer()
	{
		$this->payment->config('fees_payer', 'invalid_fees_payer');

		$this->setExpectedException('OpenBuildings\PayPal\Exception', 'Fees payer type "invalid_fees_payer" is not allowed!');

		$this->payment->fields();
	}

	public function test_ipn_url()
	{
		$this->payment->notify_url('example.com/ipn');

		$fields = $this->payment->fields();

		$this->assertArrayHasKey('ipnNotificationUrl', $fields);
		$this->assertEquals('example.com/ipn', $fields['ipnNotificationUrl']);
	}

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
}
