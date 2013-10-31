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
		self::ENVIRONMENT_LIVE,
		self::ENVIRONMENT_SANDBOX
	);

	private static $environment = self::ENVIRONMENT_SANDBOX;
	
	public static function instance($name, array $config = array())
	{
		if (empty(self::$instances[$name]))
		{
			$class = "OpenBuildings\\PayPal\\Payment_$name";
			self::$instances[$name] = new $class($config);
		}

		return self::$instances[$name];
	}

	public static function merchant_endpoint_url()
	{
		return self::MERCHANT_ENDPOINT_START.self::environment().self::MERCHANT_ENDPOINT_END;
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

		return self::ENDPOINT_START.self::environment().self::WEBSCR_ENDPOINT_END.($params ? '?'.http_build_query($params) : '');
	}

	public static function environment($environment = NULL)
	{
		if ($environment === NULL)
			return self::$environment;

		if ( ! in_array($environment, self::$_allowed_environments))
			throw new Exception('PayPal environment ":environment" is not allowed!', array(
				':environment' => $environment
			));

		self::$environment = $environment;
	}

	public static function parse_response($response_string, $url, $request_data)
	{
		$response = Util::parse_str($response_string);

		if ( ! isset($response['ACK']) OR strpos($response['ACK'], 'Success') === FALSE)
			throw new Request_Exception('PayPal API request did not succeed for :url failed: :error:code.', $url, $request_data, array(
				':url' => $url,
				':error' => isset($response['L_LONGMESSAGE0']) ? $response['L_LONGMESSAGE0'] : 'Unknown error',
				':code' => isset($response['L_ERRORCODE0']) ? ' ('.$response['L_ERRORCODE0'].')' : '',
			), $response);

		return $response;
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

		return static::parse_response($response_string, $url, $request_data);
	}
}