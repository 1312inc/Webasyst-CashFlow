<?php

/**
 * Class cashApiMissingParamException
 */
class cashApiMissingParamException extends waAPIException
{
    public function __construct($param)
    {
        parent::__construct('Missing para', sprintf('Missing required param: %s', $param), 400);
    }
}
