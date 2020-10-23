<?php

/**
 * Class cashSystemGetCurrenciesMethod
 */
class cashSystemGetCurrenciesMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiSystemGetCurrenciesResponse
     */
    public function run(): cashApiResponseInterface
    {
        return new cashApiSystemGetCurrenciesResponse((new cashApiSystemGetCurrenciesHandler())->handle(null));
    }
}
