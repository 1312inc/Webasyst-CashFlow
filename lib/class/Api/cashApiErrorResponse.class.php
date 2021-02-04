<?php

/**
 * Class cashApiErrorResponse
 */
class cashApiErrorResponse implements cashApiResponseInterface, JsonSerializable
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
     * @var int
     */
    private $status;

    /**
     * cashApiErrorResponse constructor.
     *
     * @param string $errorMessage
     * @param string $error
     * @param int    $status
     */
    public function __construct($errorMessage, $error = 'fail', $status = 400)
    {
        $this->errorMessage = $errorMessage;
        $this->error = $error;
        $this->status = $status;
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
    public function jsonSerialize(): array
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

    /**
     * @return $this
     */
    public function getResponseBody(): cashApiErrorResponse
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->status;
    }
}
