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

        if ((new cashApiTransactionDeleteHandler())->handle($request)) {
            return new cashApiTransactionDeleteResponse();
        }

        return new cashApiErrorResponse('Unrecognized error on transaction delete');
    }
}
