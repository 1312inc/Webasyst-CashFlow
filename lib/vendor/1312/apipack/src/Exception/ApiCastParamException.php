<?php

namespace ApiPack1312\Exception;

use Throwable;

class ApiCastParamException extends ApiException
{
    public function __construct($error, $errorDescription = null, $code = null, Throwable $previous = null)
    {
        parent::__construct($error, $errorDescription, 400, $previous);
    }
}
