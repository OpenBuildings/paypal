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
}