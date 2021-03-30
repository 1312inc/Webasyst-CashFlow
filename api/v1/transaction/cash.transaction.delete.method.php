<?php

/**
 * Class cashTransactionDeleteMethod
 */
class cashTransactionDeleteMethod extends cashApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

    /**
     * @return cashApiTransactionDeleteResponse|cashApiErrorResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionDeleteRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionDeleteRequest());

        return new cashApiTransactionDeleteResponse((new cashApiTransactionDeleteHandler())->handle($request));
    }
}
