<?php

namespace OpenBuildings\PayPal\Tests\Payment;

use OpenBuildings\PayPal\Payment\Payment;
use OpenBuildings\PayPal\Payment\Adaptive;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class AdaptiveTest extends \PHPUnit_Framework_TestCase
{
    public function test_webapps_url()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay', Adaptive::webapps_url());

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals('https://www.paypal.com/webapps/adaptivepayment/flow/pay?param1=value1&param2=value2', Adaptive::webapps_url(array(
            'param1' => 'value1',
            'param2' => 'value2'
        )));

        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?param1=value1&param2=value2&expType=mini', Adaptive::webapps_url(array(
            'param1' => 'value1',
            'param2' => 'value2'
        ), TRUE));
    }

    public function test_approve_url()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay?paykey=some_pay_key&expType=mini', Adaptive::approve_url('some_pay_key', TRUE));

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=some_pay_key', Adaptive::approve_url('some_pay_key'));
    }

    public function test_ap_api_url()
    {
        Payment::$environment = Payment::ENVIRONMENT_SANDBOX;
        $this->assertEquals('https://svcs.sandbox.paypal.com/AdaptivePayments', Adaptive::ap_api_url());

        Payment::$environment = Payment::ENVIRONMENT_LIVE;
        $this->assertEquals('https://svcs.paypal.com/AdaptivePayments/ExecutePayment', Adaptive::ap_api_url('ExecutePayment'));
    }

    public function test_common_fields()
    {
        $this->assertSame(array(
            'requestEnvelope.errorLanguage' => 'en_US',
            'requestEnvelope.detailLevel' => 'ReturnAll',
        ), Payment::instance('Adaptive')->common_fields());
    }

    public function test_implicit_approval()
    {
        $payment = Payment::instance('Adaptive');
        $this->assertFalse($payment->implicit_approval());

        $payment->implicit_approval(TRUE);
        $this->assertTRUE($payment->implicit_approval());

        $payment->implicit_approval(FALSE);
        $this->assertFalse($payment->implicit_approval());
    }

    public function test_action_type()
    {
        $payment = Payment::instance('Adaptive');
        $this->assertEquals('PAY', $payment->action_type());

        $payment->action_type('CREATE');
        $this->assertEquals('CREATE', $payment->action_type());
    }

    public function test_do_payment()
    {

    }
}
