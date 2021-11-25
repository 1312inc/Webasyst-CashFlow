<?php

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

    public function __construct(string $errorMessage, string $error = 'fail', int $status = 400)
    {
        $this->errorMessage = $errorMessage;
        $this->error = $error;
        $this->status = $status;
    }

    public static function fromException(Exception $ex): cashApiErrorResponse
    {
        $response = new self($ex->getMessage(), 'error', $ex->getCode());
        $response->trace = $ex->getTrace();

        return $response;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'error' => $this->error,
            'error_description' => $this->errorMessage,
        ];

        if ($this->trace && waSystemConfig::isDebug()) {
            $data['trace'] = $this->trace;
        }

        return $data;
    }

    public function getResponseBody(): cashApiErrorResponse
    {
        return $this;
    }

    public function getStatus(): int
    {
        return (int) $this->status;
    }
}
