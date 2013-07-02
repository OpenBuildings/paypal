<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Chained extends Payment_Adaptive_Parallel {

	public function fields()
	{
		$fields = parent::fields();
		$order = $this->order();

		$fields['receiverList'][0]['amount'] = number_format($order['total_price'], 2, '.', '');

		$i = 1;
		foreach ($order['receivers'] as $receiver)
		{
			if ($receiver['email'] != $this->config('email'))
			{
				$fields['receiverList'][$i]['primary'] = 'false';
				$i++;
			}
		}

		if ($i > 1)
		{
			$fields['receiverList'][0]['primary'] = 'true';
			
			if ($this->config('pay_only_primary') AND $action_type == 'PAY')
			{
				$fields['actionType'] = 'PAY_PRIMARY';
			}
		}

		return $fields;
	}
}