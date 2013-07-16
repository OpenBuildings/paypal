<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Request {

	public static function request($url, array $request_data = array(), array $headers = array())
	{
		// Create a new curl instance
		$curl = curl_init();

		$curl_options = array(
			CURLOPT_URL => $url,
			CURLOPT_POST => TRUE,
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_SSL_VERIFYHOST => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
		);

		if ($request_data)
		{
			$curl_options[CURLOPT_POSTFIELDS] = http_build_query($request_data, NULL, '&');
		}

		if ($headers)
		{
			$curl_options[CURLOPT_HTTPHEADER] = $headers;
		}

		// Set curl options
		curl_setopt_array($curl, $curl_options);

		if (($response = curl_exec($curl)) === FALSE)
		{
			// Get the error code and message
			$code  = curl_errno($curl);
			$error = curl_error($curl);

			// Close curl
			curl_close($curl);

			throw new Request_Exception('PayPal API request for :url failed: :error (:code)', $url, $request_data, array(
				':url' => $url,
				':error' => $error,
				':code' => $code
			));
		}

		// Close curl
		curl_close($curl);

		return $response;
	}

	public function simple_request($url, array $request_data = array(), array $headers = array())
	{
		return self::parse_response(self::request($url, $request_data, $headers), $url, $request_data);
	}

	public function adaptive_request($url, array $request_data = array(), array $headers = array())
	{
		return self::parse_adaptive_response(self::request($url, $request_data, $headers), $url, $request_data);
	}

	/**
	 * Validates an IPN request from PayPayl.
	 */
	public static function verify_ipn()
	{
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$post_data = array();

		foreach ($raw_post_array as $keyval)
		{
			$keyval = explode('=', $keyval);
			
			if (count($keyval) === 2)
			{
				$post_data[$keyval[0]] = urldecode($keyval[1]);
			}
		}

		$request_data = 'cmd=_notify-validate';

		foreach ($post_data as $key => $value)
		{
			if (version_compare(PHP_VERSION, '5.4') < 0 AND get_magic_quotes_gpc())
			{
				$value = stripslashes($value);
			}

			$value = urlencode($value);

			$request_data .= "&$key=$value";
		}

		$url = Payment::webscr_url();

		$curl = curl_init($url);
		curl_setopt_array($curl, array(
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POSTFIELDS => $request_data,
			CURLOPT_SSL_VERIFYPEER => 1,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_HTTPHEADER => array(
				'Connection: Close',
			)
		));

		if ( ! ($response = curl_exec($curl)))
		{
			// Get the error code and message
			$code  = curl_errno($curl);
			$error = curl_error($curl);

			// Close curl
			curl_close($curl);

			throw new Request_Exception('PayPal API request for :method failed: :error (:code)', $url, $post_data, array(
				':method' => '_notify-validate',
				':error' => $error,
				':code' => $code
			));
		}

		curl_close($curl);

		if ($response === 'VERIFIED')
			return TRUE;

		if ($response === 'INVALID')
			throw new Request_Exception('PayPal request to verify IPN was invalid!', $url, $post_data);

		return FALSE;
	}

	public function parse_response($response_string, $url, $request_data)
	{
		// Parse the response
		parse_str($response_string, $response);

		if ( ! isset($response['ACK']) OR strpos($response['ACK'], 'Success') === FALSE)
			throw new Request_Exception('PayPal API request did not succeed for :url failed: :error:code.', $url, $request_data, array(
				':url' => $url,
				':error' => isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : 'Unknown error',
				':code' => isset($response['L_ERRORCODE0']) ? ' ('.$response['L_ERRORCODE0'].')' : '',
			), $response);

		return $response;
	}

	public static function parse_adaptive_response($response_string, $url, $request_data)
	{
		// Parse the response
		parse_str($response_string, $response);

		if ( ! isset($response['responseEnvelope.ack']) OR strpos($response['responseEnvelope.ack'], 'Success') === FALSE)
			throw new Request_Exception('PayPal API request did not succeed for :url failed: :error:code.', $url, $request_data, array(
				':url' => $url,
				':error' => isset($response['error(0)_message']) ? $response['error(0)_message'] : 'Unknown error',
				':code' => isset($response['error(0)_errorId']) ? ' ('.$response['error(0)_errorId'].')' : '',
			), $response);

		return $response;
	}
}