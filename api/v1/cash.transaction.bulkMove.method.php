<?php

/**
 * Class cashTransactionBulkMoveMethod
 */
class cashTransactionBulkMoveMethod extends cashApiAbstractMethod
{
    protected $method = [self::METHOD_POST, self::METHOD_DELETE];

    /**
     * @return cashApiTransactionBulkMoveResponse|cashApiErrorResponse
     * @throws kmwaForbiddenException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionBulkMoveRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionBulkMoveRequest());

        $transactions = (new cashApiTransactionBulkMoveHandler())->handle($request);

        return new cashApiTransactionBulkMoveResponse($transactions);
    }
}
