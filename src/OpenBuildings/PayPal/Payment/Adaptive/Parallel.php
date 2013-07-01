<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Parallel extends Payment_Adaptive {

	public function fields($return_url, $cancel_url, $action_type = 'PAY')
	{
		$fields = parent::fields($return_url, $cancel_url, $action_type);
		$order = $this->order();

		$payment_type = FALSE;

		if (($payment_type = $this->_config('payment_type'))
		 AND (is_string($payment_type) OR is_array($payment_type)))
		{
			$payment_type = (is_string($payment_type))
				? $payment_type
				: ((isset($payment_type['secondary']) AND $payment_type['secondary'])
					? $payment_type['secondary']
					: FALSE
				);
		}

		$receivers_share = 0;

		$i = 1;
		foreach ($order['receivers'] as $receiver)
		{
			if ($receiver['email'] != $this->_config('email'))
			{
				$store_share = ($receiver['cut'] ?: $this->_config('default_cut')) / 100 * $receiver['total_price'];
				$fields['receiverList'][$i]['amount'] = number_format($store_share, 2, '.', '');

				$fields['receiverList'][$i]['email'] = $receiver['email'];

				if ($payment_type)
				{
					$fields['receiverList'][$i]['paymentType'] = $payment_type;
				}

				$receivers_share += $store_share;

				$i++;
			}
		}
		
		$fields['receiverList'][0]['amount'] = number_format($fields['receiverList'][0]['amount'] - $receivers_share, 2, '.', '');

		return $fields;
	}

}