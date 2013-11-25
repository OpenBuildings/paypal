<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
abstract class Payment_Adaptive extends Payment {
	
	const AP_ENDPOINT_START = 'https://svcs.';

	const AP_ENDPOINT_END = 'paypal.com/AdaptivePayments';

	const WEBAPPS_ENDPOINT_END = 'paypal.com/webapps/adaptivepayment/flow/pay';

	const ERROR_LANGUAGE = 'en_US';

	const DETAIL_LEVEL = 'ReturnAll';

	public static function webapps_url(array $params = array(), $mobile = FALSE)
	{
		if ($mobile)
		{
			$params['expType'] = 'mini';
		}

		return Payment::ENDPOINT_START
			.Payment::environment()
			.self::WEBAPPS_ENDPOINT_END
			.($params ? '?'.http_build_query($params) : '');
	}

	/**
	 * API url for AdaptivePayments based on operation and environment
	 */
	public static function ap_api_url($operation = NULL)
	{
		$api_endpoint = self::AP_ENDPOINT_START
			.Payment::environment()
			.self::AP_ENDPOINT_END;

		if ($operation)
		{
			$api_endpoint .= '/'.$operation;
		}

		return $api_endpoint;
	}

	public static function parse_response($response_string, $url, $request_data)
	{
		$response = Util::parse_str($response_string);

		if ((empty($response['responseEnvelope.ack'])
		 OR strpos($response['responseEnvelope.ack'], 'Success') === FALSE))
		{
			if ( ! empty($response['error(0).message']))
			{
				$error_message = $response['error(0).message'];
			}
			elseif ( ! empty($response['payErrorList'])
			 OR ( ! empty($response['paymentExecStatus'])
			  AND in_array($response['paymentExecStatus'], array(
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

			throw new Request_Exception(
				'PayPal API request did not succeed for :url failed: :error:code.',
				$url,
				$request_data,
				array(
					':url' => $url,
					':error' => $error_message,
					':code' => isset($response['error(0).errorId'])
						? ' ('.$response['error(0).errorId'].')'
						: '',
				),
			$response);
		}

		return $response;
	}

	/**
	 * NVP fields required for the Pay API operation
	 */
	public function common_fields()
	{
		return array(
			'requestEnvelope.errorLanguage' => self::ERROR_LANGUAGE,
			'requestEnvelope.detailLevel' => self::DETAIL_LEVEL,
		);
	}

	protected function _request($method, array $request_data = array())
	{
		$url = static::ap_api_url($method);
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
}
