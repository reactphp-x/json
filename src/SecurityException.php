<?php

namespace ReactphpX\Json;

class SecurityException extends \Exception
{
    public function __construct($message = "Security violation detected", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

