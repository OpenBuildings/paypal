<?php

namespace OpenBuildings\PayPal;

/**
 * Simple payments
 * 
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive_Simple extends Payment_Adaptive {
	
	const WEBAPPS_ENDPOINT_END = 'paypal.com/webapps/adaptivepayment/flow/pay';

	const API_OPERATION_PAY = 'Pay';

	const API_OPERATION_EXECUTE_PAYMENT = 'ExecutePayment';

	/**
	 * Use this option if you are not using the Pay request
	 * in combination with ExecutePayment
	 */
	const ACTION_TYPE_PAY = 'PAY';

	/**
	 *  Use this option to set up the payment instructions with SetPaymentOptions
	 *  and then execute the payment at a later time with the ExecutePayment.
	 */
	const ACTION_TYPE_CREATE = 'CREATE';

	/**
	 * For chained payments only, specify this value to delay payments to
	 * the secondary receivers.
	 * Only the payment to the primary receiver is processed.
	 */
	const ACTION_TYPE_PAY_PRIMARY = 'PAY_PRIMARY';

	/**
	 * Sender pays all fees (for personal, implicit simple/parallel payments; do not use for chained or unilateral payments)
	 */
	const FEES_PAYER_SENDER = 'SENDER';

	/**
	 * Primary receiver pays all fees (chained payments only)
	 */
	const FEES_PAYER_PRIMARYRECEIVER = 'PRIMARYRECEIVER';

	/**
	 * Each receiver pays their own fee (default, personal and unilateral payments)
	 */
	const FEES_PAYER_EACHRECEIVER = 'EACHRECEIVER';

	/**
	 * Secondary receivers pay all fees
	 * (use only for chained payments with one secondary receiver)
	 */
	const FEES_PAYER_SECONDARYONLY = 'SECONDARYONLY';

	// This is a payment for non-digital goods
	const PAYMENT_TYPE_GOODS = 'GOODS';

	// This is a payment for services (default)
	const PAYMENT_TYPE_SERVICE = 'SERVICE';

	// This is a person-to-person payment
	// Person-to-person payments are valid only for parallel payments
	// that have the feesPayer field set to EACHRECEIVER or SENDER
	const PAYMENT_TYPE_PERSONAL = 'PERSONAL';

	// This is a person-to-person payment for a cash advance
	const PAYMENT_TYPE_CASHADVANCE = 'CASHADVANCE';

	// This is a payment for digital goods
	const PAYMENT_TYPE_DIGITALGOODS = 'DIGITALGOODS';

	// This is a person-to-person payment for bank withdrawals,
	// available only with special permission
	const PAYMENT_TYPE_BANK_MANAGED_WITHDRAWAL = 'BANK_MANAGED_WITHDRAWAL';

	protected static $_allowed_action_types = array(
		self::ACTION_TYPE_PAY,
		self::ACTION_TYPE_CREATE,
	);

	protected static $_allowed_fees_payer_types = array(
		self::FEES_PAYER_SENDER,
		self::FEES_PAYER_PRIMARYRECEIVER,
		self::FEES_PAYER_EACHRECEIVER,
	);

	protected static $_allowed_payment_types = array(
		self::PAYMENT_TYPE_GOODS,
		self::PAYMENT_TYPE_SERVICE,
		self::PAYMENT_TYPE_SERVICE,
		self::PAYMENT_TYPE_PERSONAL,
		self::PAYMENT_TYPE_CASHADVANCE,
		self::PAYMENT_TYPE_DIGITALGOODS,
		self::PAYMENT_TYPE_BANK_MANAGED_WITHDRAWAL,
	);

	public static function approve_url($pay_key, $mobile = FALSE)
	{
		if ($mobile)
			return static::webapps_url(array(
				'paykey' => $pay_key
			), TRUE);

		return Payment::webscr_url('_ap-payment', array(
			'paykey' => $pay_key
		));
	}

	protected $_implicit_approval = FALSE;

	protected $_action_type = 'PAY';

	/**
	 * Get the NVP fields array fusion from the order and the configuration
	 * @return array
	 */
	public function fields()
	{
		$order = $this->order();

		$fields = array(
			'returnUrl' => $this->return_url(),
			'cancelUrl' => $this->cancel_url(),
			'actionType' => $this->action_type(),
			'currencyCode' => $this->config('currency'),
			'reverseAllParallelPaymentsOnError' => $this->config('reverse_on_error')
				? 'true'
				: 'false'
		);

		// Backwards compatibility: you can set a "receiver" field
		// for a single receiver
		if (isset($order['receiver']))
		{
			$order['receivers'] = array($order['receiver']);
		}

		$fields['receiverList'] = Util::receiver_list($order['receivers'], $this instanceof Payment_Adaptive_Chained);

		if ($this->implicit_approval())
		{
			if (($sender_email = $this->config('email')))
			{
				$fields['senderEmail'] = $sender_email;
			}
			elseif (($sender_account_id = $this->config('accountId')))
			{
				$fields['sender']['accountId'] = $sender_account_id;
			}
		}

		if ( ! in_array($this->config('fees_payer'), self::$_allowed_fees_payer_types))
			throw new Exception('Fees payer type ":feesPayer" is not allowed!', array(
				':feesPayer' => $this->config('fees_payer')
			));

		$fields['feesPayer'] = $this->config('fees_payer');

		if ( ! empty($order['order_number']))
		{
			$fields['trackingId'] = $order['order_number'];
		}

		if ($this->notify_url())
		{
			$fields['ipnNotificationUrl'] = $this->notify_url();
		}

		$fields = $this->_set_payment_type($fields);

		return $fields;
	}

	protected function _set_payment_type(array $fields)
	{
		$payment_type = $this->config('payment_type');

		if (isset($payment_type['secondary']))
		{
			$payment_type = $payment_type['secondary'];
		}
		elseif ( ! $payment_type AND ! is_string($payment_type))
		{
			return $fields;
		}

		foreach ($fields['receiverList'] as $index => $receiver)
		{
			$fields['receiverList'][$index]['paymentType'] = $payment_type;
		}

		return $fields;
	}

	/**
	 * Get or set whether this is an implicitly approved payment
	 *
	 * @param  boolean $implicit_approval
	 * @return boolean|$this
	 */
	public function implicit_approval($implicit_approval = NULL)
	{
		if ($implicit_approval === NULL)
			return $this->_implicit_approval;

		$this->_implicit_approval = (bool) $implicit_approval;

		return $this;
	}

	/**
	 * Get or set the action type
	 *
	 * @param  string $action_type See Payment_Adaptive::$_allowed_action_types
	 * @return string|$this
	 */
	public function action_type($action_type = NULL)
	{
		if ($action_type === NULL)
			return $this->_action_type;

		$this->_action_type = $action_type;

		return $this;
	}

	public function do_payment()
	{
		$fields = $this->fields();

		if ( ! empty($fields['receiverList']))
		{
			$receiver_list = Util::array_to_nvp($fields, 'receiverList', 'receiver');
			unset($fields['receiverList']);
			$fields = array_merge_recursive($fields, $receiver_list);
		}

		return $this->pay($fields);
	}

	/**
	 * Perform low-level Pay API request.
	 */
	public function pay($data)
	{
		return $this->_request(self::API_OPERATION_PAY, $data);
	}

	/**
	 * Perform a low-level ExecutePayment API request.
	 */
	public function execute_payment($data)
	{
		return $this->_request(self::API_OPERATION_EXECUTE_PAYMENT, $data);
	}
}
