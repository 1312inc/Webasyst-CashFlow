<?php

final class cashApiAggregateGetBalanceFlowHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetBalanceFlowRequest $request
     *
     * @return array|cashApiAggregateGetBalanceFlowDto[]
     * @throws waException
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
        $now = new DateTimeImmutable();

        $balanceFrom = $graphService->getInitialBalanceOnDate($paramsDto, $request->from);
        $balanceTo = $graphService->getInitialBalanceOnDate($paramsDto, $request->to);
        $balanceNow = $graphService->getInitialBalanceOnDate($paramsDto, $now);

        foreach ($graphData as $currency => $data) {
            $dto = new cashApiAggregateGetBalanceFlowDto($currency, $data);

            $dto->balances = [
                'from' => new cashApiAggregateGetBalanceFlowBalanceDto(
                    $request->from->format('Y-m-d'),
                    $balanceFrom[$currency] ?? null
                ),
                'to' => new cashApiAggregateGetBalanceFlowBalanceDto(
                    $request->to->format('Y-m-d'),
                    $balanceTo[$currency] ?? null
                ),
                'now' => new cashApiAggregateGetBalanceFlowBalanceDto(
                    $now->format('Y-m-d'),
                    $balanceNow[$currency] ?? null
                ),
            ];

            $response[] = $dto;
        }

        return $response;
    }
}
