<?php

/**
 * Class cashApiAbstractResponse
 */
abstract class cashApiAbstractResponse implements cashApiResponseInterface
{
    /**
     * @var int
     */
    protected $status = 200;

    /**
     * @var mixed
     */
    protected $response = null;

    /**
     * cashApiResponse constructor.
     *
     * @param int $status
     */
    public function __construct($status = null)
    {
        if ($status) {
            $this->status = $status;
        }
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    public function getResponseBody()
    {
        return $this->response;
    }
}
