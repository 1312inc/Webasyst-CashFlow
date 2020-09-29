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
        $message = $message ?: _w('Not implemented for now');
        parent::__construct($message, $code, $previous);
    }
}
