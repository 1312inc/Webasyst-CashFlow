<?php

/**
 * Class cashAccountUpdateMethod
 */
class cashAccountUpdateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountUpdateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAccountUpdateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAccountUpdateRequest());

        $account = (new cashApiAccountUpdateHandler())->handle($request);

        return new cashApiAccountUpdateResponse($account);
    }
}
