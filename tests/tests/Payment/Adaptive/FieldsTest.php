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
		$this->payment->config('fees_payer', Payment_Adaptive::FEES_PAYER_EACHRECEIVER);
	}

	public function test_sender_email()
	{
		$this->payment->implicit_approval(TRUE);

		$this->assertArrayHasKey('senderEmail', $this->payment->fields());

		$this->payment->implicit_approval(FALSE);

		$this->assertArrayNotHasKey('senderEmail', $this->payment->fields());
	}
}
