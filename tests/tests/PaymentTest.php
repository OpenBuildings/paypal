<?php

use OpenBuildings\PayPal\Payment;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class PaymentTest extends \PHPUnit_Framework_TestCase {

	public function setUp()
	{
		parent::setUp();

		$this->payment = Payment::instance('ExpressCheckout');
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::merchant_endpoint_url
	 */
	public function test_merchant_endpoint_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://api-3t.sandbox.paypal.com/nvp', Payment::merchant_endpoint_url());

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://api-3t.paypal.com/nvp', Payment::merchant_endpoint_url());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::webscr_url
	 */
	public function test_webscr_url()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals('https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&param=value', Payment::webscr_url('_ap-payment', array(
			'param' => 'value'
		)));

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals('https://www.paypal.com/cgi-bin/webscr?cmd=_ap-payment', Payment::webscr_url('_ap-payment'));
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::config
	 */
	public function test_config()
	{

	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::order
	 */
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

	/**
	 * @covers OpenBuildings\PayPal\Payment::return_url
	 */
	public function test_return_url()
	{
		$this->assertNull($this->payment->return_url());

		$this->payment->return_url('example.com/success');
		$this->assertEquals('example.com/success', $this->payment->return_url());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::cancel_url
	 */
	public function test_cancel_url()
	{
		$this->assertNull($this->payment->cancel_url());

		$this->payment->cancel_url('example.com/cancelled');
		$this->assertEquals('example.com/cancelled', $this->payment->cancel_url());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::notify_url
	 */
	public function test_notify_url()
	{
		$this->assertNull($this->payment->notify_url());

		$this->payment->notify_url('example.com/ipn');
		$this->assertEquals('example.com/ipn', $this->payment->notify_url());
	}

	/**
	 * @covers OpenBuildings\PayPal\Payment::environment
	 */
	public function test_environment()
	{
		Payment::environment(Payment::ENVIRONMENT_SANDBOX);
		$this->assertEquals(Payment::ENVIRONMENT_SANDBOX, Payment::environment());

		Payment::environment(Payment::ENVIRONMENT_LIVE);
		$this->assertEquals(Payment::ENVIRONMENT_LIVE, Payment::environment());

		$this->setExpectedException('OpenBuildings\PayPal\Exception', 'PayPal environment "not-existing-environment" is not allowed!');
		Payment::environment('not-existing-environment');
	}

	public function data_parse_response()
	{
		return array(
			array('ACK=Success', 'example.com', 'blabla', array('ACK' => 'Success')),
			array('ACK=Success with warnings&blabla=foobar', 'example.com', 'blabla', array(
				'ACK' => 'Success with warnings',
				'blabla' => 'foobar'
			)),
			array('ACK=Partial Success&blablabla=foo', 'example.com', 'blabla', array(
				'ACK' => 'Partial Success',
				'blablabla' => 'foo'
			)),
		);
	}

	/**
	 * @dataProvider data_parse_response
	 * @covers OpenBuildings\PayPal\Payment::parse_response
	 */
	public function test_parse_response($response_string, $url, $request_data, $parsed_response)
	{
		$this->assertSame($parsed_response, Payment::parse_response($response_string, $url, $request_data));
	}

	public function data_parse_response_exception()
	{
		return array(
			array(
				'ACK=Error',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Unknown error.',
			),
			array(
				'blabla=bla',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Unknown error.',
			),
			array(
				'ack=error&L_LONGMESSAGE0=Some error message',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Some error message.',
			),
			array(
				'ack=error&L_LONGMESSAGE0=Some error message&L_ERRORCODE0=mycode',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Some error message (mycode).',
			),
			array(
				'L_LONGMESSAGE0=Some error message&L_ERRORCODE0=mycode',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Some error message (mycode).',
			),
			array(
				'acks=Success&L_LONGMESSAGE0=Some error message&L_ERRORCODE0=mycode',
				'example.com',
				'foo=bar',
				'PayPal API request did not succeed for example.com failed: Some error message (mycode).',
			),
		);
	}

	/**
	 * @dataProvider data_parse_response_exception
	 * @covers OpenBuildings\PayPal\Payment::parse_response
	 */
	public function test_parse_response_exception($response_string, $url, $request_data, $exception_message)
	{
		$this->setExpectedException('OpenBuildings\PayPal\Request_Exception', $exception_message);
		Payment::parse_response($response_string, $url, $request_data);
	}
}
