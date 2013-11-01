<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Parallel extends Payment_Adaptive {

	public function fields()
	{
		$fields = parent::fields();
		$order = $this->order();

		$payment_type = FALSE;

		if (($payment_type = $this->config('payment_type'))
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

		foreach ($order['receivers'] as $index => $receiver)
		{
			$fields['receiverList'][$index]['amount'] = number_format($receiver['amount'], 2, '.', '');

			$fields['receiverList'][$index]['email'] = $receiver['email'];

			if ($payment_type)
			{
				$fields['receiverList'][$index]['paymentType'] = $payment_type;
			}
		}

		return $fields;
	}
}
