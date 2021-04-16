<?php

/**
 * Class cashTransactionBulkMoveMethod
 */
class cashTransactionBulkMoveMethod extends cashApiAbstractMethod
{
    private const MAX_IDS = 500;

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

        if (count($request->ids) > self::MAX_IDS) {
            return new cashApiErrorResponse(sprintf_wp('Too many transactions to move. Max limit is %d', self::MAX_IDS));
        }

        $transactions = (new cashApiTransactionBulkMoveHandler())->handle($request);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($transactions))
        );

        return new cashApiTransactionBulkMoveResponse($transactions);
    }
}
