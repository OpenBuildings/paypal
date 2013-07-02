<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_ExpressCheckout extends Payment {

	const API_VERSION = '98.0';

	public function get_express_checkout_details(array $params)
	{
		if ( ! isset($params['TOKEN']))
			throw new Exception(
				'You must provide a TOKEN parameter for method "'.__METHOD__.'"'
			);

		return $this->_request('GetExpressCheckoutDetails', $params);
	}

	protected function _set_params()
	{
		$order = $this->order();

		$params = array(
			// Total amount for the transaction
			'PAYMENTREQUEST_0_AMT' => number_format($order['total_price'], 2, '.', ''),

			// Price of the items being sold
			'PAYMENTREQUEST_0_ITEMAMT' => number_format($order['items_price'], 2, '.', ''),

			// Shipping costs for the whole transaction
			'PAYMENTREQUEST_0_SHIPPINGAMT' => number_format($order['shipping_price'], 2, '.', ''),

			'PAYMENTREQUEST_0_CURRENCYCODE' => $this->config('currency'),

			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			
			'RETURNURL' => $this->return_url(),

			'CANCELURL' => $this->cancel_url(),

			'useraction' => 'commit',

			// PayPal won't display shipping fields to the customer
			// For digital goods this field is required and it must be set to 1.
			'NOSHIPPING' => 1,

			'ADDROVERRIDE' => 0,
		);

		if ($this->notify_url() !== NULL)
		{
			$params['PAYMENTREQUEST_0_NOTIFYURL'] = $this->notify_url();
		}

		return $params;
	}

	/**
	 * Make an SetExpressCheckout call.
	 *
	 * @param array $params NVP parameters
	 */
	public function set_express_checkout()
	{
		return $this->_request('SetExpressCheckout', $this->set_params());
	}

	public function do_express_checkout_payment($token, $payer_id)
	{
		$order = $this->order();

		return $this->_request('DoExpressCheckoutPayment', array(
			'TOKEN'                          => $token,
			'PAYERID'                        => $payer_id,

			// Total amount of the order
			'PAYMENTREQUEST_0_AMT'           => number_format($order['total_price'], 2, '.', ''),

			// Price of the items being sold
			'PAYMENTREQUEST_0_ITEMAMT'       => number_format($order['items_price'], 2, '.', ''),

			// Shipping costs for the whole transaction
			'PAYMENTREQUEST_0_SHIPPINGAMT'   => number_format($order['shipping_price'], 2, '.', ''),

			'PAYMENTREQUEST_0_CURRENCYCODE'  => $this->config['currency'],

			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale'
		));
	}

	protected function _request($method, array $params = array())
	{
		// Create POST data
		$post = array(
			'METHOD'    => $method,
			'VERSION'   => Payment_ExpressCheckout::API_VERSION,
			'USER'      => $this->config('username'),
			'PWD'       => $this->config('password'),
			'SIGNATURE' => $this->config('signature'),
		) + $params;

		return parent::request(Payment::merchant_endpoint_url(), array(
			'METHOD'    => $method,
			'VERSION'   => Payment_ExpressCheckout::API_VERSION,
			'USER'      => $this->config('username'),
			'PWD'       => $this->config('password'),
			'SIGNATURE' => $this->config('signature'),
		) + $params);
	}
}