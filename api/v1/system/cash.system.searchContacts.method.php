<?php

/**
 * Class cashSystemGetSettingsMethod
 */
class cashSystemSearchContactsMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiSystemSearchContactsResponse
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiSystemSearchContactsRequest $request */
        $request = $this->fillRequestWithParams(new cashApiSystemSearchContactsRequest());

        return new cashApiSystemSearchContactsResponse((new cashApiSystemSearchContactsHandler())->handle($request));
    }
}
