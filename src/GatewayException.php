<?php
/*
 * This file is part of the Passport package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\Passport;

use Exception;

/**
 * Gateway Exception
 *
 * Handling request errors from the API.
 */
class GatewayException extends Exception
{
    /**
     * Create new error instance.
     *
     * @param string  $message Exception message
     * @param integer $code    User defined exception code.
     *
     * @return void
     */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}