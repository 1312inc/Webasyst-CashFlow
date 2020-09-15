<?php

/**
 * Class cashTransactionCreateMethod
 *
 * @return array of transactions
 */
class cashTransactionCreateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionCreateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionCreateRequest());

        $response = (new cashApiTransactionCreateHandler())->handle($request);

        return new cashApiTransactionCreateResponse($response);
    }
}
