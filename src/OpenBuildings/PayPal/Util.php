<?php

namespace OpenBuildings\PayPal;

class Util {

	public static function array_to_nvp(array $array, $key, $prefix)
	{
		$result = array();
		
		foreach ($array[$key] as $index => $values)
		{
			$nvp_key = $key.'.'.$prefix.'('.$index.')';

			foreach ($values as $name => $value)
			{
				$result[$nvp_key.'.'.$name] = $value;
			}
		}

		return $result;
	}

	/**
	 * Parse response string without parse_str()
	 * parse_str converts dots and spaces to underscores.
	 * The reason is because all keys must be valid PHP variable names.
	 * http://php.net/manual/en/function.parse-str.php#76978
	 *
	 * Notice: It does not work with strings representing nested arrays like:
	 *     "first=value&arr[]=foo+bar&arr[]=baz"
	 *
	 * It is not needed for the purpose of parsing PayPal responses.
	 *
	 * @param  string $response_string a key value pair like param1=value1&param2=value2
	 * @return array
	 */
	public static function parse_str($response_string)
	{
		$response_raw_array = explode('&', $response_string);
		$response = array();
		foreach ($response_raw_array as $keyval)
		{
			$keyval = explode('=', $keyval);
			
			if (count($keyval) === 2)
			{
				$response[$keyval[0]] = urldecode($keyval[1]);
			}
		}

		return $response;
	}

	/**
	 * Validates an IPN request from Paypal.
	 */
	public static function verify_ipn($raw_data = NULL)
	{
		if ($raw_data === NULL)
		{
			$raw_data = file_get_contents('php://input');
		}
	
		if (empty($raw_data))
			return FALSE;

		$raw_post_array = explode('&', $raw_data);
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
}
