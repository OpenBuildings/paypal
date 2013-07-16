<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Parallel extends Payment_Adaptive {

	public function fields()
	{
		$fields = parent::fields();
		$order = $this->order();

		$payment_type = $this->payment_type();

		$receivers_share = 0;

		$i = 1;
		foreach ($order['receivers'] as $receiver)
		{
			if ($receiver['email'] != $this->config('email'))
			{
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


	/**
	 * Parse and return a payment type
	 * @param  string|array $payment_type optional base to parse; if not provided config would be used
	 * @return string|NULL a valid payment type or NULL
	 * @throws OpenBuildings\PayPal\Exception If the provided payment type is not allowed
	 */
	public function payment_type($payment_type = NULL)
	{
		if ($payment_type === NULL)
		{
			$payment_type = $this->config('payment_type');
		}

		if ($payment_type
		 AND (is_string($payment_type) OR is_array($payment_type)))
		{
			$payment_type = (is_string($payment_type))
				? $payment_type
				: (( ! empty($payment_type['secondary']))
					? $payment_type['secondary']
					: FALSE
				);

			if ($payment_type)
			{
				if ( ! in_array($payment_type, Payment_Adaptive::$_allowed_payment_types))
					throw new Exception('Payment type ":payment_type" is not allowed!', array(
						':payment_type' => $payment_type
					));

				return $payment_type;
			}
		}

		return NULL;
	}
}