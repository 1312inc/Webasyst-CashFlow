<?php

/**
 * Class cashAggregateGetChartDataMethod
 */
class cashAggregateGetChartDataMethod extends cashApiAbstractMethod
{
    const MAX_DAYS = 1000;

    protected $method = self::METHOD_GET;

    /**
     * @return cashApiAccountCreateResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAggregateGetChartDataRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAggregateGetChartDataRequest());
        $request->to = DateTimeImmutable::createFromFormat('Y-m-d', $request->to);
        $request->from = DateTimeImmutable::createFromFormat('Y-m-d', $request->from);

        if ($request->group_by === cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY
            && $request->from->diff($request->to)->days > 1000
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
