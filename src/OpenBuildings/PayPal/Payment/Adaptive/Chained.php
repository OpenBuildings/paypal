<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Chained extends Payment_Adaptive_Parallel
{
    protected static $_allowed_action_types = array(
        self::ACTION_TYPE_PAY,
        self::ACTION_TYPE_CREATE,
        self::ACTION_TYPE_PAY_PRIMARY,
    );

    protected static $_allowed_fees_payer_types = array(
        self::FEES_PAYER_SENDER,
        self::FEES_PAYER_PRIMARYRECEIVER,
        self::FEES_PAYER_EACHRECEIVER,
        self::FEES_PAYER_SECONDARYONLY
    );

    public function fields()
    {
        $fields = parent::fields();
        $order = $this->order();

        $fields['receiverList'][0]['amount'] = number_format($order['total_price'], 2, '.', '');

        $i = 1;
        foreach ($order['receivers'] as $receiver) {
            if ($receiver['email'] != $this->config('email')) {
                $fields['receiverList'][$i]['primary'] = 'false';
                $i++;
            }
        }

        if ($i > 1) {
            $fields['receiverList'][0]['primary'] = 'true';

            if ($this->config('pay_only_primary') AND $action_type == 'PAY') {
                $fields['actionType'] = 'PAY_PRIMARY';
            }
        }

        return $fields;
    }
}
