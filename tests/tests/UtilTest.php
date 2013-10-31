<?php

use OpenBuildings\PayPal\Util;

class UtilTest extends \PHPUnit_Framework_TestCase {


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