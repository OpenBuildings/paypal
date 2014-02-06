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

	public function data_parse_response()
	{
		return array(
			array(
				array(
					'responseEnvelope.timestamp' => '2014-02-06T01:46:20.359-08:00',
					'responseEnvelope.ack' => 'Success',
					'responseEnvelope.correlationId' => '??????????????',
					'responseEnvelope.build' => '9535556',
					'payKey' => 'AP-?????????????????',
					'paymentExecStatus' => 'ERROR',
					'payErrorList.payError(0).receiver.amount' => '476.82',
					'payErrorList.payError(0).receiver.email' => 'jo@example.com',
					'payErrorList.payError(0).error.errorId' => '570016',
					'payErrorList.payError(0).error.domain' => 'PLATFORM',
					'payErrorList.payError(0).error.severity' => 'Error',
					'payErrorList.payError(0).error.category' => 'Application',
					'payErrorList.payError(0).error.message' => 'The payment cannot be processed because it would result in negative balance for primaryReceiver',
					'payErrorList.payError(0).error.parameter(0).@name' => 'abc',
					'payErrorList.payError(0).error.parameter(0)' => 'primaryReceiver'
				),
				'PayPal API request did not succeed for http://example.com failed: Status was ERROR',
			),
			array(
				array(
					'responseEnvelope.timestamp' => '2014-02-06T01:46:19.982-08:00',
					'responseEnvelope.ack' => 'Success',
					'responseEnvelope.correlationId' => '??????????????',
					'responseEnvelope.build' => '9535556',
					'payKey' => 'AP-?????????????????',
					'paymentExecStatus' => 'COMPLETED',
					'paymentInfoList.paymentInfo(0).transactionId' => '?????????????????',
					'paymentInfoList.paymentInfo(0).transactionStatus' => 'COMPLETED',
					'paymentInfoList.paymentInfo(0).receiver.amount' => '136.71',
					'paymentInfoList.paymentInfo(0).receiver.email' => 'sales@example.com',
					'paymentInfoList.paymentInfo(0).receiver.primary' => 'false',
					'paymentInfoList.paymentInfo(0).receiver.accountId' => '??????????????',
					'paymentInfoList.paymentInfo(0).pendingRefund' => 'false',
					'paymentInfoList.paymentInfo(0).senderTransactionId' => '?????????????????',
					'paymentInfoList.paymentInfo(0).senderTransactionStatus' => 'COMPLETED',
					'sender.accountId' => '??????????????'
				),
				NULL,
			),
			array(
				array(
					'responseEnvelope.timestamp' => '2013-07-04T07:20:38.135-07:00',
					'responseEnvelope.ack' => 'Success',
					'responseEnvelope.correlationId' => '??????????????',
					'responseEnvelope.build' => '6520082',
					'payKey' => 'AP-?????????????????',
					'paymentExecStatus' => 'CREATED'
				),
				NULL,
			),
			array(
				array(
					'responseEnvelope.timestamp' => '2009-08-14T09:00:37.748-07:00',
					'responseEnvelope.ack' => 'Success',
					'responseEnvelope.correlationId' => '7967b2d03745a',
					'responseEnvelope.build' => 'DEV',
					'cancelUrl' => 'your_cancel_url',
					'currencyCode' => 'USD',
					'logDefaultShippingAddress' => 'false',
					'memo' => 'Simple payment example.',
					'paymentInfoList.paymentInfo(0).receiver.amount' => '100.00',
					'paymentInfoList.paymentInfo(0).receiver.email' => 'receiver@domain',
					'paymentInfoList.paymentInfo(0).receiver.primary' => 'false',
					'returnUrl' => 'your_return_url',
					'senderEmail' => 'sender@domain',
					'status' => 'CREATED',
					'payKey' => 'AP-3TY011106S4428730',
					'actionType' => 'PAY',
					'feesPayer' => 'EACHRECEIVER',
				),
				NULL,
			)
		);
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment_Adaptive::parse_response
	 * @dataProvider data_parse_response
	 */
	public function test_parse_response($response, $expectedException)
	{
		if ($expectedException)
		{
			$this->setExpectedException('OpenBuildings\PayPal\Request_Exception', $expectedException);
		}

		Payment_Adaptive::parse_response(http_build_query($response), 'http://example.com', array('data'));
	}
}
