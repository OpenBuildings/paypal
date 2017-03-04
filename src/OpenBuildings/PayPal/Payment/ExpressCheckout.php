<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hkdobrev@gmail.com>
 * @copyright 2013 OpenBuildings, Inc.
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

	/**
	 * Make an SetExpressCheckout call.
	 */
	public function set_express_checkout(array $params = array())
	{
        $startParams = array(
            'RETURNURL' => $this->return_url(),
            'CANCELURL' => $this->cancel_url()
        );
        $params = array_merge($startParams, $params);

		return $this->_request('SetExpressCheckout', $this->_set_params($params));
	}

	public function do_express_checkout_payment($token, $payer_id)
	{
        $params = array(
            'TOKEN'                          => $token,
            'PAYERID'                        => $payer_id
        );

        return $this->_request('DoExpressCheckoutPayment', $this->_set_params($params));
	}

	protected function _set_params(array $params = array())
	{
        $defaultParams = array(
            'PAYMENTREQUEST_0_CURRENCYCODE' => $this->config('currency'),

            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',

            'useraction' => 'commit',

            // PayPal won't display shipping fields to the customer
            // For digital goods this field is required and it must be set to 1.
            'NOSHIPPING' => 1,

            'REQCONFIRMSHIPPING' => 0,

            'ADDROVERRIDE' => 0,
        );

        $totalPrice = 0;
        foreach($this->order() as $index => $item) {
            $defaultParams['L_PAYMENTREQUEST_0_NAME' . $index] = $item["name"];
            $defaultParams['L_PAYMENTREQUEST_0_AMT' . $index]  = $item["price"];
            $defaultParams['L_PAYMENTREQUEST_0_QTY' . $index]  = $item["quantity"];
            $defaultParams['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $index] = "Digital";

            $totalPrice += $item["price"] * $item["quantity"];
        }
        $params['PAYMENTREQUEST_0_AMT'] = number_format($totalPrice, 2, '.', '');

		$params = array_merge($defaultParams, $params);

		if ($this->notify_url())
		{
			$params['PAYMENTREQUEST_0_NOTIFYURL'] = $this->notify_url();
		}

		return $params;
	}

	protected function _request($method, array $params = array())
	{
		return $this->request(static::merchant_endpoint_url(), array(
			'METHOD'    => $method,
			'VERSION'   => Payment_ExpressCheckout::API_VERSION,
			'USER'      => $this->config('username'),
			'PWD'       => $this->config('password'),
			'SIGNATURE' => $this->config('signature'),
		) + $params);
	}
}
