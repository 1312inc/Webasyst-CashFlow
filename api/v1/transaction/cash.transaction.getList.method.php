<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiCastParamException;
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
        $from = $this->fromGet('from', false, ApiParamsCaster::CAST_STRING_TRIM);
        if ($from) {
            $from = DateTimeImmutable::createFromFormat('Y-m-d|', $from);
            if (!$from) {
                throw new ApiCastParamException(sprintf('Wrong format for param %s', 'from'));
            }
        }

        $to = $this->fromGet('to', false, ApiParamsCaster::CAST_STRING_TRIM);
        if ($to) {
            $to = DateTimeImmutable::createFromFormat('Y-m-d|', $to);
            if (!$to) {
                throw new ApiCastParamException(sprintf('Wrong format for param %s', 'to'));
            }
        }

        $request = new cashApiTransactionGetListRequest(
            $from ?: null,
            $to ?: null,
            $this->fromGet('offset', false, ApiParamsCaster::CAST_INT),
            $this->fromGet('limit', false, ApiParamsCaster::CAST_INT),
            $this->fromGet('filter', false, ApiParamsCaster::CAST_STRING_TRIM)
        );

        if ($request->getFilter()
            && !$request->getTo()
            && !$request->getFrom()
            && strpos($request->getFilter(), cashAggregateFilter::FILTER_SEARCH . '/') === false
            && strpos($request->getFilter(), cashAggregateFilter::FILTER_IMPORT . '/') === false
            && strpos($request->getFilter(), cashAggregateFilter::FILTER_TRASH . '/') === false
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
