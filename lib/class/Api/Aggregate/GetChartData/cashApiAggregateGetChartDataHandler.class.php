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
        $graphData = $graphService->getAggregateChartData($paramsDto);

        $response = [];
        foreach ($graphData as $graphDatum) {
            $response[] = new cashApiAggregateGetChartDataDto(
                $graphDatum['groupkey'],
                abs($graphDatum['incomeAmount']),
                abs($graphDatum['expenseAmount']),
                $graphDatum['balance'],
                $graphDatum['currency']
            );
        }

        return $response;
    }
}
