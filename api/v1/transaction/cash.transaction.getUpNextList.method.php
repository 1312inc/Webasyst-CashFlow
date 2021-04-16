<?php

/**
 * Class cashTransactionGetUpNextListMethod
 */
class cashTransactionGetUpNextListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetListResponse
     * @throws kmwaForbiddenException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetUpNextListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetUpNextListRequest());
        $request->today = DateTimeImmutable::createFromFormat('Y-m-d|', $request->today);

        if (!$request->today) {
            return new cashApiErrorResponse("Wrong date format. Expected 'Y-m-d'");
        }

        $transactions = (new cashApiTransactionGetUpNextListHandler())->handle($request);

        if ($transactions['data']) {
            cash()->getEventDispatcher()->dispatch(
                new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($transactions['data']))
            );
        }

        return new cashApiTransactionGetUpNextListResponse($transactions['data']);
    }
}
