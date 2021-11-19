<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashTransactionGetListMethod extends cashApiNewAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiTransactionGetListResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiTransactionGetListRequest(
            $this->getApiParamsFetcher()->get('from', false, ApiParamsCaster::CAST_DATETIME, 'Y-m-d'),
            $this->getApiParamsFetcher()->get('to', false, ApiParamsCaster::CAST_DATETIME, 'Y-m-d'),
            $this->getApiParamsFetcher()->get('offset', false, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->get('limit', false, ApiParamsCaster::CAST_INT),
            $this->getApiParamsFetcher()->get('filter', false, ApiParamsCaster::CAST_STRING_TRIM)
        );

        if ($request->getFilter()
            && !$request->getTo()
            && !$request->getFrom()
            && strpos($request->getFilter(), cashAggregateFilter::FILTER_SEARCH . '/') === false
            && strpos($request->getFilter(), cashAggregateFilter::FILTER_IMPORT . '/') === false
        ) {
            throw new ApiMissingParamException('to, from');
        }

        $transactions = (new cashApiTransactionGetListHandler())->handle($request);

        if ($transactions['data']) {
            cash()->getEventDispatcher()->dispatch(
                new cashEvent(
                    cashEventStorage::API_TRANSACTION_BEFORE_RESPONSE,
                    new ArrayIterator($transactions['data'])
                )
            );
        }

        return new cashApiTransactionGetListResponse(
            $transactions['data'],
            (int) $transactions['total'],
            $request->getOffset(),
            $request->getLimit()
        );
    }
}
