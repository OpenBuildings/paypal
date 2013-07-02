<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Adaptive extends Payment {
	
	const AP_ENDPOINT_START = 'https://svcs.';

	const AP_ENDPOINT_END = 'paypal.com/AdaptivePayments';

	const WEBAPPS_ENDPOINT_END = 'paypal.com/webapps/adaptivepayment/flow/pay';

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

	private static $_allowed_fees_payer_types = array(
		self::FEES_PAYER_SENDER,
		self::FEES_PAYER_PRIMARYRECEIVER,
		self::FEES_PAYER_EACHRECEIVER,
		self::FEES_PAYER_SECONDARYONLY,
	);

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

	public static function webapps_url(array $params = array(), $mobile = FALSE)
	{
		if ($mobile)
		{
			$params['expType'] = 'mini';
		}

		return Payment::ENDPOINT_START.Payment::environment().Payment_Adaptive::WEBAPPS_ENDPOINT_END.($params ? '?'.http_build_query($params) : '');
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
					'email' => $this->config('email'),
					'amount' => number_format($order['total_price'], 2, '.', ''),
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
			throw new Exception('[PayPal Pay]: Fees payer type ":feesPayer" is not allowed!', array(
				':feesPayer' => $this->config('fees_payer')
			));

		$fields['feesPayer'] = $this->config('fees_payer');

		if ( ! empty($order['order_number']))
		{
			$fields['trackingId'] = $purchase->order_number;
		}

		if ($this->notify_url())
		{
			$fields['ipnNotificationUrl'] = $this->notify_url();
		}

		if (($payment_type = $this->config('payment_type')))
		{
			if (is_string($payment_type)
			 OR (is_array($payment_type)
			  AND isset($payment_type['primary']) AND $payment_type = $payment_type['primary']))
			{
				$fields['receiverList'][0]['paymentType'] = $paymentType;
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
		$response = $this->_request('Pay', $data);

		if ( ! isset($response['responseEnvelope_ack'])
		 OR strpos($response['responseEnvelope_ack'], 'Success') === FALSE)
			throw new Request_Exception('PayPal AdaptivePayments API request for :method method failed: :error (:code)', array(
				':method' => 'Pay',
				':error' => $response['error(0)_message'],
				':code' => $response['error(0)_errorId']
			));

		return $response;
	}

	/**
	 * Performs an ExecutePayment API request.
	 */
	public function execute_payment($data)
	{
		$response = $this->_request('ExecutePayment', $data);

		if ((isset($response['payErrorList']) AND $response['payErrorList'])
		 OR (isset($response['paymentExecStatus'])
		  AND in_array($response['paymentExecStatus'], array(
		 	'ERROR',
		 	'REVERSALERROR'
		 ))))
			throw new Request_Exception('PayPal ExecutePayment API request failed. :errors',  array(
				':errors' => isset($response['payErrorList']) ?
				 print_r($response['payErrorList'], TRUE) :
				 ('Status was '.$response['paymentExecStatus'])
			));

		return $response;
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

		try
		{
			return parent::request($url, $request_data, $headers);
		}
		catch (Request_Exception $exception)
		{
			if ($exception->response)
				return $exception->response;

			throw $exception;
		}
	}
}