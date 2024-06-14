<?php

/**
 * Class cashSystemPokeMethod
 */
class cashSystemPokeMethod extends cashApiAbstractMethod implements cashApiResponseInterface
{
    protected $method = self::METHOD_GET;

    public function run(): cashApiResponseInterface
    {
        $event = new cashEventOnCount();
        cash()->waDispatchEvent($event);
        $this->http_status_code = 204;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->http_status_code;
    }

    public function getResponseBody()
    {
        return null;
    }
}
