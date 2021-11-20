<?php

/**
 * Class cashValidateException
 */
class cashValidateException extends waException
{
    public function __construct($message = '', $code = 500, $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }
}
