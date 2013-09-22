<?php

namespace OpenBuildings\PayPal;

use OpenBuildings\PayPal\Payment\Payment;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class InstanceTest extends \PHPUnit_Framework_TestCase
{
    public function test_express_checkout()
    {
        $express_checkout = Payment::instance('ExpressCheckout');

        $this->assertInstanceOf('OpenBuildings\PayPal\Payment\ExpressCheckout', $express_checkout);
    }

    public function test_adaptive()
    {
        $express_checkout = Payment::instance('Adaptive');

        $this->assertInstanceOf('OpenBuildings\PayPal\Payment\Adaptive', $express_checkout);
    }

    public function test_express_adaptive_parallel()
    {
        $express_checkout = Payment::instance('Adaptive\Parallel');

        $this->assertInstanceOf('OpenBuildings\PayPal\Payment\Adaptive\Parallel', $express_checkout);
    }

    public function test_express_adaptive_chained()
    {
        $express_checkout = Payment::instance('Adaptive\Chained');

        $this->assertInstanceOf('OpenBuildings\PayPal\Payment\Adaptive\Chained', $express_checkout);
    }

    public function test_instance_pattern()
    {
        $this->assertSame(Payment::instance('ExpressCheckout'), Payment::instance('ExpressCheckout'));
    }
}
