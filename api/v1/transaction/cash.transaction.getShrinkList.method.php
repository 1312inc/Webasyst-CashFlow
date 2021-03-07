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

        return new cashApiTransactionGetShrinkListResponse(

        );
    }
}
