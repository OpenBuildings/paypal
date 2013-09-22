<?php

namespace OpenBuildings\PayPal;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Exception extends \Exception
{
    public function __construct($message, $variables = array(), \Exception $previous = NULL)
    {
        if ($variables) {
            $message = strtr($message, $variables);
        }

        parent::__construct($message, 0, $previous);
    }
}
