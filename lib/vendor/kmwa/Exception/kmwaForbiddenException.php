<?php

/**
 * Class kmwaForbiddenException
 */
class kmwaForbiddenException extends Exception
{
    /**
     * kmwaForbiddenException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 500, $previous = null)
    {
        $message = $message ?: _w('Access denied');
        parent::__construct($message, 403, $previous);
    }
}
