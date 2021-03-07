<?php

/**
 * Class cashSystemGetSettingsMethod
 */
class cashSystemGetSettingsMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiSystemGetSettingsResponse
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        return new cashApiSystemGetSettingsResponse((new cashApiSystemGetSettingsHandler())->handle(null));
    }
}
