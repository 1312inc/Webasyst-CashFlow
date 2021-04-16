<?php

final class cashApiResponse extends cashApiAbstractResponse
{
    public function __construct($status = null, string $response = 'ok')
    {
        parent::__construct($status);

        $this->response = $response;
    }
}
