<?php

/**
 * Class kmwaNotImplementedException
 */
class kmwaNotImplementedException extends Exception
{
    /**
     * kmwaNotImplementedException constructor.
     *
     * @param string $message
     * @param int    $code
     * @param null   $previous
     */
    public function __construct($message = '', $code = 500, $previous = null)
    {
        parent::__construct(_w('Not implemented for now'), $code, $previous);
    }
}
