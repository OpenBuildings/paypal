<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 Despark Ltd.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Payment_Recurring extends Payment_ExpressCheckout {

	protected function _set_params($return_url, $cancel_url, $notify_url = NULL)
	{
		$params = parent::set_params($return_url, $cancel_url, $notify_url);

		return array_replace($params, array(
			'PAYMENTREQUEST_0_AMT' => 0,
			'PAYMENTREQUEST_0_ITEMAMT' => 0,
			'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
			'L_BILLINGTYPE0' => 'RecurringPayments',
			'L_BILLINGAGREEMENTDESCRIPTION0' => $this->config('description'),
			'L_PAYMENTREQUEST_0_ITEMCATEGORYn' => 'Digital',
			'MAXAMT' => $this->transaction_amount(),
		));
	}

	public function transaction_amount()
	{
		$amount = $this->config('amount_per_month');

		if ($this->config('charged_yearly'))
		{
			$amount *= 12;
		}

		return $amount;
	}

	public function create_recurring_payments_profile(array $params)
	{
		return $this->_request('CreateRecurringPaymentsProfile', array(
			'TOKEN'             => $params['token'],
			'SUBSCRIBERNAME'    => isset($params['subscriber_name']) ? $params['subscriber_name'] : NULL,
			'PROFILESTARTDATE'  => gmdate('Y-m-d\TH:i:s.00\Z', strtotime('+1 hour')),
			'PROFILEREFERENCE'  => isset($params['application_id']) ? $params['application_id'] : NULL,
			'DESC'              => $this->config('description'),
			'MAXFAILEDATTEMPTS' => $this->config('max_failed_attempts'),
			'AUTOBILLOUTAMT'    => 'AddToNextBilling',
			'BILLINGPERIOD'     => $this->config('billing_period'),
			'BILLINGFREQUENCY'     => $this->config('billing_frequency'),
			'AMT'               => $this->config('amount'),
			'CURRENCYCODE'      => $this->config('paypal.currency'),
		));
	}

	public function manage_recurring_payments_profile_status(array $params)
	{
		return $this->_request('ManageRecurringPaymentsProfileStatus', $params);
	}

	public function get_recurring_payments_profile_details($profile_id)
	{
		return $this->_request('GetRecurringPaymentsProfileDetails', array(
			'PROFILEID' => $profile_id
		));
	}

	public function bill_outstanding_amount(array $params)
	{
		return $this->_request('BillOutstandingAmount', $params);
	}
}