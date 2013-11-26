<?php

namespace OpenBuildings\PayPal;

class Payment_Adaptive_Refund extends Payment_Adaptive {

	const API_OPERATION_REFUND = 'Refund';

	/**
	 * Perform a low-level Refund API request.
	 */
	public function refund($data)
	{
		return $this->_request(self::API_OPERATION_REFUND, $data);
	}

	public function do_refund(array $transaction, array $receivers = NULL, $chained = FALSE)
	{
		if (empty($transaction['payKey']) AND empty($transaction['trackingId']))
			throw new Exception('You must provide either "payKey" or "trackingId" to Refund API operation.');

		$data = array(
			'currencyCode' => $this->config('currency')
		);

		if (isset($transaction['payKey']))
		{
			$data['payKey'] = $transaction['payKey'];
		}
		elseif (isset($transaction['trackingId']))
		{
			$data['trackingId'] = $transaction['trackingId'];
		}

		if (isset($transaction['transactionId']))
		{
			$data['transactionId'] = $transaction['transactionId'];
		}

		if ($receivers)
		{
			$data['receiverList'] = Util::receiver_list($receivers, $chained);

			$receiver_list = Util::array_to_nvp($data, 'receiverList', 'receiver');
			unset($data['receiverList']);
			$data = array_merge_recursive($data, $receiver_list);
		}

		return $this->refund($data);
	}
}
