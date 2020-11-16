<?php

final class cashApiAggregateGetBalanceOnDateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetBalanceOnDateRequest $request
     *
     * @return array
     * @throws waException
     */
    public function handle($request)
    {
        $paramsDto = new cashAggregateChartDataFilterParamsDto(
            wa()->getUser(),
            $request->date,
            $request->date,
            cashAggregateChartDataFilterParamsDto::GROUP_BY_DAY,
            cashAggregateFilter::createFromHash($request->filter)
        );

        $graphService = new cashGraphService();

        return $graphService->getInitialBalanceOnDate($paramsDto, $request->date);
    }
}
