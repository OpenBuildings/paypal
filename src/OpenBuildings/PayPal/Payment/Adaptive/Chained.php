<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Chained extends Payment_Adaptive_Parallel {

	protected static $_allowed_action_types = array(
		self::ACTION_TYPE_PAY,
		self::ACTION_TYPE_CREATE,
		self::ACTION_TYPE_PAY_PRIMARY,
	);

	protected static $_allowed_fees_payer_types = array(
		self::FEES_PAYER_SENDER,
		self::FEES_PAYER_PRIMARYRECEIVER,
		self::FEES_PAYER_EACHRECEIVER,
		self::FEES_PAYER_SECONDARYONLY
	);

	public function fields()
	{
		$fields = parent::fields();
		$order = $this->order();

		if ($this->config('pay_only_primary') AND $this->action_type() == 'PAY')
		{
			$fields['actionType'] = 'PAY_PRIMARY';
		}

		return $fields;
	}

	protected function _set_payment_type(array $fields)
	{
		$fields = parent::_set_payment_type($fields);

		$order = $this->order();
		$payment_type = $this->config('payment_type');

		if (isset($payment_type['primary'])
		 AND ($payment_type = $payment_type['primary']))
		{
			foreach ($order['receivers'] as $index => $receiver)
			{
				if ($receiver['primary'])
				{
					$fields['receiverList'][$index]['paymentType'] = $payment_type;
					break;
				}
			}
		}

		return $fields;
	}
}
