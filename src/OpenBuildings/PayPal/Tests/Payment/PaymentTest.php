<?php

namespace OpenBuildings\PayPal\Tests\Payment;

use OpenBuildings\PayPal\Payment\Payment;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class PaymentTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->payment = Payment::instance('ExpressCheckout');
    }

    public function data_array_to_nvp()
    {
        return array(
            array(
                array(
                    'list' => array(
                        array(
                            'first_name' => 'A',
                            'last_name' => 'B'
                        )
                    )
                ),
                'list',
                'item',
                array(
                    'list.item(0).first_name' => 'A',
                    'list.item(0).last_name' => 'B',
                )
            ),
            array(
                array(
                    'list' => array(
                        array(
                            'a' => 'b',
                            'c' => 'd'
                        ),
                        array(
                            'e' => 'f',
                        ),
                    )
                ),
                'list',
                'obj',
                array(
                    'list.obj(0).a' => 'b',
                    'list.obj(0).c' => 'd',
                    'list.obj(1).e' => 'f',
                )
            ),
        );
    }

    /**
     * @dataProvider data_array_to_nvp
     */
    public function test_array_to_nvp($array, $key, $prefix, $expected_nvp)
    {
        $this->assertSame($expected_nvp, Payment::array_to_nvp($array, $key, $prefix));
    }

    public function test_merchant_endpoint_url()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', Payment::merchant_endpoint_url());

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals('https://api-3t.paypal.com/nvp', Payment::merchant_endpoint_url());
    }

    public function test_webscr_url()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&param=value', Payment::webscr_url('_ap-payment', array(
            'param' => 'value'
        )));

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment', Payment::webscr_url('_ap-payment'));
    }

    public function test_config()
    {

    }

    public function test_order()
    {
        $this->assertSame(array(), $this->payment->order());

        $this->payment->order(array(
            'total_price' => 10
        ));
        $this->assertSame(array(
            'total_price' => 10
        ), $this->payment->order());
    }

    public function test_return_url()
    {
        $this->assertNull($this->payment->return_url());

        $this->payment->return_url('example.com/success');
        $this->assertEquals('example.com/success', $this->payment->return_url());
    }

    public function test_cancel_url()
    {
        $this->assertNull($this->payment->cancel_url());

        $this->payment->cancel_url('example.com/cancelled');
        $this->assertEquals('example.com/cancelled', $this->payment->cancel_url());
    }

    public function test_notify_url()
    {
        $this->assertNull($this->payment->notify_url());

        $this->payment->notify_url('example.com/ipn');
        $this->assertEquals('example.com/ipn', $this->payment->notify_url());
    }

    public function test_environment()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals(Payment::ENVIRONMENT_SANDBOX, Payment::environment());

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals(Payment::ENVIRONMENT_LIVE, Payment::environment());

        Payment::$environment = 'not-existing-environment';
        $this->setExpectedException('OpenBuildings\PayPal\Payment\Exception', 'PayPal environment "not-existing-environment" is not allowed!');
        Payment::environment();
    }

}
