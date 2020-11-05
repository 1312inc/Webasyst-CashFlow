<?php

/**
 * Class cashTransactionBulkDeleteMethod
 */
class cashTransactionBulkDeleteMethod extends cashApiAbstractMethod
{
    private const MAX_IDS = 500;

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

        if (count($request->ids) > self::MAX_IDS) {
            return new cashApiErrorResponse(sprintf_wp('Too many transactions to delete. Max %d', self::MAX_IDS));
        }

        if ((new cashApiTransactionBulkDeleteHandler())->handle($request)) {
            return new cashApiTransactionBulkDeleteResponse();
        }

        return new cashApiErrorResponse('Some error on transaction delete');
    }
}
