<?php

/**
 * Class cashApiErrorResponse
 */
class cashApiErrorResponse extends cashApiAbstractResponse implements JsonSerializable
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var null|string
     */
    private $trace;

    /**
     * cashApiErrorResponse constructor.
     *
     * @param string $errorMessage
     * @param string $error
     * @param int    $status
     */
    public function __construct($errorMessage, $error = 'fail', $status = 400)
    {
        parent::__construct($status);

        $this->errorMessage = $errorMessage;
        $this->error = $error;
    }

    /**
     * @param Exception $ex
     *
     * @return cashApiErrorResponse
     */
    public static function fromException(Exception $ex): cashApiErrorResponse
    {
        $response = new self($ex->getMessage(), 'error', $ex->getCode());
        $response->trace = $ex->getTrace();

        return $response;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            'error' => $this->error,
            'error_message' => $this->errorMessage,
        ];

        if ($this->trace && waSystemConfig::isDebug()) {
            $data['trace'] = $this->trace;
        }

        return $data;
    }
}
