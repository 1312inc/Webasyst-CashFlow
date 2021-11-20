<?php

namespace ApiPack1312\Exception;

use Exception;
use Throwable;

class ApiException extends Exception
{
    /**
     * @var string
     */
    private $errorDescription;

    public function __construct($error, $errorDescription = null, $code = null, Throwable $previous = null)
    {
        $this->errorDescription = $errorDescription;

        parent::__construct($error, $code, $previous);
    }

    public function getErrorDescription(): string
    {
        return $this->errorDescription;
    }
}
