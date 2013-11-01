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
