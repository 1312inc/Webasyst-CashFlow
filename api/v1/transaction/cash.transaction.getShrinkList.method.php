<?php

class cashTransactionGetShrinkListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetShrinkListResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetShrinkListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetShrinkListRequest());

        $transactions = (new cashApiTransactionGetShrinkListHandler())->handle($request);

        if ($transactions) {
            cash()->getEventDispatcher()->dispatch(
                new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($transactions))
            );
        }

        return new cashApiTransactionGetShrinkListResponse($transactions);
    }
}
