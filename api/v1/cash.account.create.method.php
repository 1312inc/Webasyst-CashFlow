<?php

/**
 * Class cashAccountCreateMethod
 */
class cashAccountCreateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAccountCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountCreateRequest());

        $response = (new cashApiAccountCreateHandler())->handle($request);

        return new cashApiAccountCreateResponse($response);
    }
}
