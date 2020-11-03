<?php

/**
 * Class cashApiAggregateGetChartDataHandler
 */
final class cashApiAggregateGetChartDataHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetChartDataRequest $request
     *
     * @return cashApiAggregateGetChartDataDto[]
     * @throws waException
     */
    public function handle($request)
    {
        $paramsDto = new cashAggregateChartDataFilterParamsDto(
            wa()->getUser(),
            $request->from,
            $request->to,
            $request->group_by,
            cashAggregateFilter::createFromHash($request->filter)
        );

        $graphService = new cashGraphService();

        return $graphService->getAggregateChartData($paramsDto);
    }
}
