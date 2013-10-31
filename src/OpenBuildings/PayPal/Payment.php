<?php

namespace OpenBuildings\PayPal;

/**
 * @abstract
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
abstract class Payment {

	const ENDPOINT_START = 'https://www.';

	const WEBSCR_ENDPOINT_END = 'paypal.com/cgi-bin/webscr';

	const MERCHANT_ENDPOINT_START = 'https://api-3t.';
	
	const MERCHANT_ENDPOINT_END = 'paypal.com/nvp';
	
	const ENVIRONMENT_LIVE = '';

	const ENVIRONMENT_SANDBOX = 'sandbox.';

	public static $instances = array();

	public static $environment = Payment::ENVIRONMENT_SANDBOX;

	public static $config = array(
		'app_id' => '',
		'username' => '',
		'password' => '',
		'signature' => '',
		'email' => '',
		'client_id' => '',
		'secret' => '',
		'currency' => 'USD',
	);

	protected static $_allowed_environments = array(
		Payment::ENVIRONMENT_LIVE,
		Payment::ENVIRONMENT_SANDBOX
	);
	
	public static function instance($name, array $config = array())
	{
		if (empty(self::$instances[$name]))
		{
			$class = "OpenBuildings\\PayPal\\Payment_$name";
			self::$instances[$name] = new $class($config);
		}

		return self::$instances[$name];
	}

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
	 * @param  string $response_string a key value pair like param1=value1&param2=value2
	 * @return array
	 */
	public static function parse_response($response_string)
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

	public static function merchant_endpoint_url()
	{
		return Payment::MERCHANT_ENDPOINT_START.Payment::environment().Payment::MERCHANT_ENDPOINT_END;
	}

	/**
	 * Webscr url based on command, params and environment
	 */
	public static function webscr_url($command = FALSE, array $params = array())
	{
		if ($command)
		{
			$params = array_reverse($params, TRUE);
			$params['cmd'] = $command;
			$params = array_reverse($params, TRUE);
		}

		return Payment::ENDPOINT_START.Payment::environment().Payment::WEBSCR_ENDPOINT_END.($params ? '?'.http_build_query($params) : '');
	}

	public static function environment()
	{
		if ( ! in_array(Payment::$environment, Payment::$_allowed_environments))
			throw new Exception('PayPal environment ":environment" is not allowed!', array(
				':environment' => Payment::$environment
			));

		return Payment::$environment;
	}

	/**
	 * Validates an IPN request from Paypal.
	 */
	public static function verify_ipn()
	{
		$raw_post_data = file_get_contents('php://input');

		if (empty($raw_post_data))
			return FALSE;

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

	protected $_config;

	protected $_order = array();

	protected $_return_url = NULL;

	protected $_cancel_url = NULL;

	protected $_notify_url = NULL;

	public function __construct(array $config = array())
	{
		$this->config($config);
	}

	public function config($key, $value = NULL)
	{
		if ($value === NULL AND ! is_array($key))
			return (isset($this->_config[$key]) AND array_key_exists($key, $this->_config))
				? $this->_config[$key]
				: NULL;

		if (is_array($key))
		{
			$this->_config = array_replace(self::$config, $key);
		}
		else
		{
			$this->_config[$key] = $value;
		}

		return $this;
	}

	public function order(array $order = NULL)
	{
		if ($order === NULL)
			return $this->_order;

		$this->_order = $order;

		return $this;
	}

	public function return_url($return_url = NULL)
	{
		if ($return_url === NULL)
			return $this->_return_url;

		$this->_return_url = $return_url;

		return $this;
	}

	public function cancel_url($cancel_url = NULL)
	{
		if ($cancel_url === NULL)
			return $this->_cancel_url;

		$this->_cancel_url = $cancel_url;

		return $this;
	}

	public function notify_url($notify_url = NULL)
	{
		if ($notify_url === NULL)
			return $this->_notify_url;

		$this->_notify_url = $notify_url;

		return $this;
	}

	public function request($url, array $request_data = array(), array $headers = array())
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

		if (($response_string = curl_exec($curl)) === FALSE)
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

		return $this->_parse_response($response_string, $url, $request_data);
	}

	protected function _parse_response($response_string, $url, $request_data)
	{
		$response = Payment::parse_response($response_string);

		if ( ! isset($response['ACK']) OR strpos($response['ACK'], 'Success') === FALSE)
			throw new Request_Exception('PayPal API request did not succeed for :url failed: :error:code.', $url, $request_data, array(
				':url' => $url,
				':error' => isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : 'Unknown error',
				':code' => isset($response['L_ERRORCODE0']) ? ' ('.$response['L_ERRORCODE0'].')' : '',
			), $response);

		return $response;
	}
}