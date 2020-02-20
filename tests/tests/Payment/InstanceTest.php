<?php

use OpenBuildings\PayPal\Payment;
use PHPUnit\Framework\TestCase;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_InstanceTest extends TestCase {

	public function test_express_checkout()
	{
		$express_checkout = Payment::instance('ExpressCheckout');

		$this->assertInstanceOf('OpenBuildings\PayPal\Payment_ExpressCheckout', $express_checkout);
	}

	public function test_adaptive_simple()
	{
		$express_checkout = Payment::instance('Adaptive_Simple');

		$this->assertInstanceOf('OpenBuildings\PayPal\Payment_Adaptive_Simple', $express_checkout);
	}

	public function test_express_adaptive_parallel()
	{
		$express_checkout = Payment::instance('Adaptive_Parallel');

		$this->assertInstanceOf('OpenBuildings\PayPal\Payment_Adaptive_Parallel', $express_checkout);
	}

	public function test_express_adaptive_chained()
	{
		$express_checkout = Payment::instance('Adaptive_Chained');

		$this->assertInstanceOf('OpenBuildings\PayPal\Payment_Adaptive_Chained', $express_checkout);
	}

	public function test_instance_pattern()
	{
		$this->assertSame(Payment::instance('ExpressCheckout'), Payment::instance('ExpressCheckout'));
	}
}