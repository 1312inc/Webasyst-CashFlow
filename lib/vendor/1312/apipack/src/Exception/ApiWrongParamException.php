<?php

namespace ApiPack1312\Exception;

use Throwable;

class ApiWrongParamException extends ApiException
{
    public function __construct(string $param, string $description, Throwable $previous = null)
    {
        parent::__construct(
            sprintf("Wrong param: '%s'", $param),
            sprintf("Wrong param: '%s'. %s", $param, $description),
            400,
            $previous
        );
    }
}
