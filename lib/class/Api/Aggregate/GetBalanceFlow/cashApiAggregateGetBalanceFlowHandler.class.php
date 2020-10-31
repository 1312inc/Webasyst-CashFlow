<?php

final class cashApiAggregateGetBalanceFlowHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetBalanceFlowRequest $request
     *
     * @return array|void
     */
    public function handle($request)
    {
        $paramsDto = new cashAggregateChartDataFilterParamsDto(
            wa()->getUser(),
            $request->from,
            $request->to,
            $request->group_by,
            new cashAggregateFilter()
        );

        $graphService = new cashGraphService();
        $graphData = $graphService->getAggregateBalanceFlow($paramsDto);

        $response = [];
        foreach ($graphData as $currency => $data) {
            $firstDatum = reset($data);
            $balanceFrom = $firstDatum['amount'];

            $lastDatum = end($data);
            $balanceTo = $lastDatum['amount'];
            $response[] = new cashApiAggregateGetBalanceFlowDto(
                $currency,
                $request->from->format('Y-m-d'),
                $balanceFrom,
                $request->to->format('Y-m-d'),
                $balanceTo,
                $data
            );
        }

        return $response;
    }
}
