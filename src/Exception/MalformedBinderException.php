<?php

namespace Tsuka\DB\Exception;

/**
 * Exception to be thrown when invocating a wrongly formed Binder
 *
 * @package Tsuka\DB\Exception
 */

class MalformedBinderException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}