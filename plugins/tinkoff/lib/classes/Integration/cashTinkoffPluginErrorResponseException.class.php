<?php

class cashTinkoffPluginErrorResponseException extends waException
{
    public function __construct(waNet $waNet, waException $exception)
    {
        $data = $waNet->getResponse();

        parent::__construct(
            $data['errorMessage'] ?? $exception->getMessage(),
            400,
            $exception
        );

        if ($data) {
            cashTinkoffPlugin::log(sprintf('Got error response: %s', $this->message));
        }

        $debug = $waNet->__debugInfo();
        unset($debug['options'], $debug['preview']);
        cashTinkoffPlugin::debug($debug);
    }
}
