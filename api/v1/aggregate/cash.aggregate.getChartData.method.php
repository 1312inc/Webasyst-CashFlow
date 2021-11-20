<?php

use ApiPack1312\ApiParamsCaster;
use ApiPack1312\Exception\ApiException;
use ApiPack1312\Exception\ApiMissingParamException;
use ApiPack1312\Exception\ApiWrongParamException;

final class cashAggregateGetChartDataMethod extends cashApiNewAbstractMethod
{
    const MAX_DAYS = 10000;

    protected $method = self::METHOD_GET;

    /**
     * @return cashApiAccountCreateResponse
     *
     * @throws ApiException
     * @throws ApiMissingParamException
     * @throws ApiWrongParamException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        $request = new cashApiAggregateGetChartDataRequest(
            $this->fromGet('from', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d|'),
            $this->fromGet('to', true, ApiParamsCaster::CAST_DATETIME, 'Y-m-d|'),
            $this->fromGet(
                'group_by',
                true,
                ApiParamsCaster::CAST_ENUM,
                [
                    cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY,
                    cashAggregateChartDataFilterParamsDto::GROUP_BY_MONTH,
                    cashAggregateChartDataFilterParamsDto::GROUP_BY_YEAR,
                ]
            ),
            $this->fromGet('filter', true, ApiParamsCaster::CAST_STRING)
        );

        if ($request->getGroupBy() === cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY
            && $request->getFrom()->diff($request->getTo())->days > self::MAX_DAYS
        ) {
            return new cashApiErrorResponse(
                sprintf_wp(
                    'For %s+ days, only "%s" & "%s" grouping is available',
                    self::MAX_DAYS,
                    cashAggregateChartDataFilterParamsDto::GROUP_BY_MONTH,
                    cashAggregateChartDataFilterParamsDto::GROUP_BY_YEAR
                )
            );
        }

        $response = (new cashApiAggregateGetChartDataHandler())->handle($request);

        return new cashApiAggregateGetChartDataResponse($response);
    }
}
