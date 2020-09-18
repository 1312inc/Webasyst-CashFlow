<?php

/**
 * Class cashTransactionGetListMethod
 */
class cashTransactionGetListMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetListResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetListRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetListRequest());
        $request->start = (int) $request->start;
        if ($request->limit > 500) {
            $request->limit = 500;
        }

        $transactions = (new cashApiTransactionGetListHandler())->handle($request);

        return new cashApiTransactionGetListResponse($transactions);
    }
}
