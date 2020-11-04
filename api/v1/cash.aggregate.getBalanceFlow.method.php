<?php

/**
 * Class cashAggregateGetBalanceFlowMethod
 */
final class cashAggregateGetBalanceFlowMethod extends cashApiAbstractMethod
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
        /** @var cashApiAggregateGetBalanceFlowRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAggregateGetBalanceFlowRequest());
        $request->from = DateTimeImmutable::createFromFormat('Y-m-d|', $request->from);
        $request->to = DateTimeImmutable::createFromFormat('Y-m-d|', $request->to);

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

        $response = (new cashApiAggregateGetBalanceFlowHandler())->handle($request);

        return new cashApiAggregateGetBalanceFlowResponse($response);
    }
}
