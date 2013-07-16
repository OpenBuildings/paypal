<?php

namespace OpenBuildings\PayPal;

/**
 * @abstract
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
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
}