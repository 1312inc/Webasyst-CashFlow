<?php

/**
 * Class cashTransactionBulkDeleteMethod
 */
class cashTransactionBulkDeleteMethod extends cashApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

    /**
     * @return cashApiTransactionBulkDeleteResponse|cashApiErrorResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionBulkDeleteRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionBulkDeleteRequest());

        if ((new cashApiTransactionBulkDeleteHandler())->handle($request)) {
            return new cashApiTransactionBulkDeleteResponse();
        }

        return new cashApiErrorResponse('Some error on transaction delete');
    }
}
