<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive extends Payment {
	
	const AP_ENDPOINT_START = 'https://svcs.';

	const AP_ENDPOINT_END = 'paypal.com/AdaptivePayments';

	const WEBAPPS_ENDPOINT_END = 'paypal.com/webapps/adaptivepayment/flow/pay';

	/**
	 * Use this option if you are not using the Pay request in combination with ExecutePayment
	 */
	const ACTION_TYPE_PAY = 'PAY';

	/**
	 *  Use this option to set up the payment instructions with SetPaymentOptions and then execute the payment at a later time with the ExecutePayment.
	 */
	const ACTION_TYPE_CREATE = 'CREATE';

	/**
	 * For chained payments only, specify this value to delay payments to the secondary receivers; only the payment to the primary receiver is processed.
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
	 * Secondary receivers pay all fees (use only for chained payments with one secondary receiver)
	 */
	const FEES_PAYER_SECONDARYONLY = 'SECONDARYONLY';

	// This is a payment for non-digital goods
	const PAYMENT_TYPE_GOODS = 'GOODS';

	// This is a payment for services (default)
	const PAYMENT_TYPE_SERVICE = 'SERVICE';

	// This is a person-to-person payment
	const PAYMENT_TYPE_PERSONAL = 'PERSONAL';

	// This is a person-to-person payment for a cash advance
	const PAYMENT_TYPE_CASHADVANCE = 'CASHADVANCE';

	// This is a payment for digital goods
	const PAYMENT_TYPE_DIGITALGOODS = 'DIGITALGOODS';

	// This is a person-to-person payment for bank withdrawals,
	// available only with special permission
	const PAYMENT_TYPE_BANK_MANAGED_WITHDRAWAL = 'BANK_MANAGED_WITHDRAWAL';

	const ERROR_LANGUAGE = 'en_US';

	const DETAIL_LEVEL = 'ReturnAll';

	protected static $_allowed_action_types = array(
		self::ACTION_TYPE_PAY,
		self::ACTION_TYPE_CREATE,
	);

	protected static $_allowed_payment_types = array(
	);

	protected static $_allowed_fees_payer_types = array(
		self::FEES_PAYER_SENDER,
		self::FEES_PAYER_PRIMARYRECEIVER,
		self::FEES_PAYER_EACHRECEIVER,
	);

	public static function webapps_url(array $params = array(), $mobile = FALSE)
	{
		if ($mobile)
		{
			$params['expType'] = 'mini';
		}

		return Payment::ENDPOINT_START.Payment::environment().Payment_Adaptive::WEBAPPS_ENDPOINT_END.($params ? '?'.http_build_query($params) : '');
	}

	public static function approve_url($pay_key, $mobile = FALSE)
	{
		if ($mobile)
			return Payment_Adaptive::webapps_url(array(
				'paykey' => $pay_key
			), TRUE);

		return Payment::webscr_url('_ap-payment', array(
			'paykey' => $pay_key
		));
	}

	/**
	 * API url for AdaptivePayments based on method and environment
	 */
	public static function ap_api_url($method = NULL)
	{
		$api_endpoint = Payment_Adaptive::AP_ENDPOINT_START.Payment::environment().Payment_Adaptive::AP_ENDPOINT_END;

		if ($method)
		{
			$api_endpoint .= '/'.$method;
		}

		return $api_endpoint;
	}

	protected $_implicit_approval = FALSE;

	protected $_action_type = 'PAY';

	/**
	 * NVP fields required for the Pay API operation
	 */
	public function common_fields()
	{
		return array(
			'requestEnvelope.errorLanguage' => Payment_Adaptive::ERROR_LANGUAGE,
			'requestEnvelope.detailLevel' => Payment_Adaptive::DETAIL_LEVEL,
		);
	}

	/**
	 * NVP fields for a Simple payment
	 */
	public function fields()
	{
		$order = $this->order();

		$fields = array(
			'returnUrl' => $this->return_url(),
			'cancelUrl' => $this->cancel_url(),
			'actionType' => $this->action_type(),
			'receiverList' => array(
				array(
					'email' => $order['receiver']['email'],
					'amount' => number_format($order['receiver']['amount'], 2, '.', ''),
				)
			),
			'currencyCode' => $this->config('currency'),
			'reverseAllParallelPaymentsOnError' => $this->config('reverse_on_error') ? 'true' : 'false'
		);

		if ($this->implicit_approval())
		{
			$fields['senderEmail'] = $this->config('email');
		}

		if ( ! in_array($this->config('fees_payer'), Payment_Adaptive::$_allowed_fees_payer_types))
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

		if (($payment_type = $this->config('payment_type')))
		{
			if (is_string($payment_type)
			 OR (is_array($payment_type)
			  AND ! empty($payment_type['primary'])))
			{
				$fields['receiverList'][0]['paymentType'] = $payment_type['primary'];
			}
		}

		return $fields;
	}

	/**
	 * Change the implicit approval
	 * @param  [type] $implicit_approval [description]
	 * @return [type]                    [description]
	 */
	public function implicit_approval($implicit_approval = NULL)
	{
		if ($implicit_approval === NULL)
			return $this->_implicit_approval;

		$this->_implicit_approval = $implicit_approval;

		return $this;
	}

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
		$receiver_list = Payment::array_to_nvp($fields, 'receiverList', 'receiver');
		unset($fields['receiverList']);
		return $this->pay(array_merge_recursive($fields, $receiver_list));
	}

	/**
	 * Performs a Pay API request.
	 */
	public function pay($data)
	{
		return $this->_request('Pay', $data);
	}

	/**
	 * Performs an ExecutePayment API request.
	 */
	public function execute_payment($data)
	{
		return $this->_request('ExecutePayment', $data);
	}

	protected function _request($method, array $request_data = array())
	{
		$url = Payment_Adaptive::ap_api_url($method);
		$request_data = array_merge($request_data, $this->common_fields());

		$headers = array(
			'X-PAYPAL-REQUEST-DATA-FORMAT: NV',
			'X-PAYPAL-RESPONSE-DATA-FORMAT: NV',
			'X-PAYPAL-SECURITY-USERID: '.$this->config('username'),
			'X-PAYPAL-SECURITY-PASSWORD: '.$this->config('password'),
			'X-PAYPAL-SECURITY-SIGNATURE: '.$this->config('signature'),
			'X-PAYPAL-SERVICE-VERSION: 1.6.0',
			'X-PAYPAL-APPLICATION-ID: '.$this->config('app_id'),
		);

			return $this->request($url, $request_data, $headers);
	}

	protected function _parse_response($response_string, $url, $request_data)
	{
		$response = Payment::parse_response($response_string);

		if ((empty($response['responseEnvelope.ack']) OR strpos($response['responseEnvelope.ack'], 'Success') === FALSE))
		{
			if ( ! empty($response['error(0).message']))
			{
				$error_message = $response['error(0).message'];
			}
			elseif ( ! empty($response['payErrorList'])
			 OR ( ! empty($response['paymentExecStatus']) AND in_array($response['paymentExecStatus'], array(
			 	'ERROR',
			 	'REVERSALERROR'
			 )))
			)
			{
				if (empty($response['payErrorList']))
				{
					$error_message = 'Status was '.$response['paymentExecStatus'];
				}
				else
				{
					$error_message = print_r($response['payErrorList'], TRUE);
				}
			}
			else
			{
				$error_message = 'Unknown error';
			}

			throw new Request_Exception('PayPal API request did not succeed for :url failed: :error:code.', $url, $request_data, array(
				':url' => $url,
				':error' => $error_message,
				':code' => isset($response['error(0).errorId']) ? ' ('.$response['error(0).errorId'].')' : '',
			), $response);
		}

		return $response;
	}
}