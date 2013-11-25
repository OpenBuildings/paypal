<?php

namespace OpenBuildings\PayPal;

class Payment_Adaptive_PaymentDetails extends Payment_Adaptive {

	const API_OPERATION_PAYMENT_DETAILS = 'PaymentDetails';

	/**
	 * Perform a low-level PaymentDetails API request.
	 */
	public function payment_details($data)
	{
		return $this->_request(self::API_OPERATION_PAYMENT_DETAILS, $data);
	}

	public function get_payment_details(array $transaction)
	{
		if (empty($transaction['payKey']) AND empty($transaction['trackingId']))
			throw new Exception('You must provide either "payKey" or "trackingId" to PaymentDetails API operation.');

		return $this->payment_details($transaction);
	}
	
	public function fields()
	{
		$fields = parent::fields();
		$order = $this->order();

		foreach ($order['receivers'] as $index => $receiver)
		{
			$fields['receiverList'][$index]['primary'] = empty($receiver['primary'])
				? 'false'
				: 'true';
		}

		if ($this->config('pay_only_primary') AND $this->action_type() == 'PAY')
		{
			$fields['actionType'] = 'PAY_PRIMARY';
		}

		return $fields;
	}
}