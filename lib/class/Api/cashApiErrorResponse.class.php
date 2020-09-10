<?php

/**
 * Class cashApiErrorResponse
 */
class cashApiErrorResponse implements JsonSerializable
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $message;

    /**
     * @var null|string
     */
    private $trace;

    /**
     * cashApiErrorResponse constructor.
     *
     * @param string $message
     * @param string $status
     */
    public function __construct($message, $status = 'fail')
    {
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @param Exception $ex
     *
     * @return cashApiErrorResponse
     */
    public static function fromException(Exception $ex): cashApiErrorResponse
    {
        $response = new self($ex->getMessage(), 'error');
        $response->trace = $ex->getTrace();

        return $response;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            'status' => $this->status,
            'message' => $this->message,
        ];

        if ($this->trace && waSystemConfig::isDebug()) {
            $data['trace'] = $this->trace;
        }

        return $data;
    }
}
