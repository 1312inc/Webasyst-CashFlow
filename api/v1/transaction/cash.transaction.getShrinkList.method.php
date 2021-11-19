<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashTransactionGetShrinkListMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetShrinkListResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiTransactionGetShrinkListRequest(
            $this->getApiParamsFetcher()->get('from', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d|'),
            $this->getApiParamsFetcher()->get('to', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d|'),
            $this->getApiParamsFetcher()->get('from', true, ApiParamsCaster::CAST_STRING_TRIM)
        );

        $transactions = (new cashApiTransactionGetShrinkListHandler())->handle($request);

        if ($transactions) {
            cash()->getEventDispatcher()->dispatch(
                new cashEvent(cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE, new ArrayIterator($transactions))
            );
        }

        return new cashApiTransactionGetShrinkListResponse($transactions);
    }
}
