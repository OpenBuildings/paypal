<?php

use OpenBuildings\PayPal\Util;

class UtilTest extends \PHPUnit_Framework_TestCase {

	public function data_receiver_list()
	{
		return array(
			// empty receiver list
			array(
				array(), FALSE, array(),
				array(), TRUE, array(),
			),
			// basic usage
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'test-sender@example.com',
					)
				), FALSE, array(
					array(
						'amount' => '50.00',
						'email' => 'test-sender@example.com',
					)
				),
			),
			// basic usage with number_format
			array(
				array(
					array(
						'amount' => 50.056,
						'email' => 'test-sender@example.com',
					)
				), FALSE, array(
					array(
						'amount' => '50.06',
						'email' => 'test-sender@example.com',
					)
				),
			),
			// basic usage with accountId
			array(
				array(
					array(
						'amount' => 50.056,
						'accountId' => 'abcde',
					)
				), FALSE, array(
					array(
						'amount' => '50.06',
						'accountId' => 'abcde',
					)
				),
			),
			// basic usage with both accountId and email
			array(
				array(
					array(
						'amount' => 50.056,
						'email' => 'test-sender@example.com',
						'accountId' => 'abcde',
					)
				), FALSE, array(
					array(
						'amount' => '50.06',
						'email' => 'test-sender@example.com',
						'accountId' => 'abcde',
					)
				),
			),
			// basic usage with chained payments
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'test-sender@example.com',
					)
				), TRUE, array(
					array(
						'amount' => '50.00',
						'email' => 'test-sender@example.com',
						'primary' => 'false',
					)
				),
			),
			// multiple receivers
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'abc@example.com',
					),
					array(
						'amount' => 50.00,
						'email' => 'test-sender@example.com',
					),
				), FALSE, array(
					array(
						'amount' => '50.00',
						'email' => 'abc@example.com',
					),
					array(
						'amount' => '50.00',
						'email' => 'test-sender@example.com',
					),
				),
			),
			// multiple receivers with chained payments
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'abc@example.com',
					),
					array(
						'amount' => 50.00,
						'email' => 'test-sender@example.com',
					),
				), TRUE, array(
					array(
						'amount' => '50.00',
						'email' => 'abc@example.com',
						'primary' => 'false'
					),
					array(
						'amount' => '50.00',
						'email' => 'test-sender@example.com',
						'primary' => 'false',
					),
				),
			),
			// multiple receivers with chained payments
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'abc@example.com',
						'primary' => TRUE,
					),
					array(
						'amount' => 50.00,
						'email' => 'test-sender@example.com',
					),
				), TRUE, array(
					array(
						'amount' => '50.00',
						'email' => 'abc@example.com',
						'primary' => 'true',
					),
					array(
						'amount' => '50.00',
						'email' => 'test-sender@example.com',
						'primary' => 'false',
					),
				),
			),
			// big test
			array(
				array(
					array(
						'amount' => 50.00,
						'email' => 'test1@example.com',
						'primary' => TRUE,
					),
					array(
						'amount' => 4350.031,
						'email' => 'test2@example.com',
						'accountId' => 'poiu',
					),
					array(
						'amount' => 34.5,
						'email' => 'test3@example.com',
						'primary' => 'd',
					),
					array(
						'amount' => -2050,
						'email' => 'test4@example.com',
					),
					array(
						'amount' => '20.447',
						'accountId' => 'test5',
					),
				), TRUE, array(
					array(
						'amount' => '50.00',
						'email' => 'test1@example.com',
						'primary' => 'true',
					),
					array(
						'amount' => '4350.03',
						'email' => 'test2@example.com',
						'accountId' => 'poiu',
						'primary' => 'false',
					),
					array(
						'amount' => '34.50',
						'email' => 'test3@example.com',
						'primary' => 'true',
					),
					array(
						'amount' => '2050.00',
						'email' => 'test4@example.com',
						'primary' => 'false',
					),
					array(
						'amount' => '20.45',
						'accountId' => 'test5',
						'primary' => 'false',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider data_receiver_list
	 * @covers OpenBuildings\PayPal\Util::receiver_list
	 */
	public function test_receiver_list($receivers, $chained, $expected_receiver_list)
	{
		$this->assertSame($expected_receiver_list, Util::receiver_list($receivers, $chained));
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
	 * @covers OpenBuildings\PayPal\Util::array_to_nvp
	 */
	public function test_array_to_nvp($array, $key, $prefix, $expected_nvp)
	{
		$this->assertSame($expected_nvp, Util::array_to_nvp($array, $key, $prefix));
	}

	public function data_parse_str()
	{
		return array(
			array(
				'param=value',
				array(
					'param' => 'value',
				),
			),
			array(
				'param1=value1&param2=value2',
				array(
					'param1' => 'value1',
					'param2' => 'value2',
				),
			),
			array(
				'par.am=val ue',
				array(
					'par.am' => 'val ue',
				),
			),
			array(
				'par.am=val ue&par*.am2  =_$value2',
				array(
					'par.am' => 'val ue',
					'par*.am2  ' => '_$value2',
				),
			),
			array(
				'param= value',
				array(
					'param' => ' value',
				),
			),
			array(
				'param = value',
				array(
					'param ' => ' value',
				),
			),
		);
	}

	/**
	 * @dataProvider data_parse_str
	 * @covers OpenBuildings\PayPal\Util::parse_str
	 */
	public function test_parse_str($string, $expected_array)
	{
		$this->assertSame($expected_array, Util::parse_str($string));
	}

}