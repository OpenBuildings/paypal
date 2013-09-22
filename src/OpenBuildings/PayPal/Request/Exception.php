<?php

namespace OpenBuildings\PayPal\Request;

/**
 * @author Haralan Dobrev <hdobrev@despark.com>
 * @copyright (c) 2013 OpenBuildings Inc.
 * @license http://spdx.org/licenses/BSD-3-Clause
 */
class Exception extends Exception
{
    public $url;

    public $params = array();

    public $response;

    public function __construct($message, $url, $params, $variables = array(), $response = NULL)
    {
        $this->url = $url;
        $this->params = $params;
        $this->response = $response;

        parent::__construct($message, $variables);
    }
}
