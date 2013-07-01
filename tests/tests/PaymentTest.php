<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class PaymentTest extends \PHPUnit_Framework_TestCase {

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
		
	}

	public function test_config()
	{
		
	}

	public function test_order()
	{
		
	}

	public function test_environment()
	{
		
	}

}