<?php

/**
 * Class cashTransactionGetMethod
 */
class cashTransactionGetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetResponse
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiTransactionGetRequest $request */
        $request = $this->fillRequestWithParams(new cashApiTransactionGetRequest());

        $transactions = (new cashApiTransactionGetHandler())->handle($request);

        return new cashApiTransactionGetResponse($transactions);
    }
}
