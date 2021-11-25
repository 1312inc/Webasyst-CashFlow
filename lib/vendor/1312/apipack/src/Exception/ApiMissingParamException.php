<?php

namespace ApiPack1312\Exception;

use Throwable;

class ApiMissingParamException extends ApiException
{
    public function __construct(string $param, Throwable $previous = null)
    {
        parent::__construct(
            sprintf("Missing required param: '%s'", $param),
            sprintf("Missing required param: '%s'", $param),
            400,
            $previous
        );
    }
}
