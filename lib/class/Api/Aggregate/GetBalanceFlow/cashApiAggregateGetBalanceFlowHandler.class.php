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
        $initialBalanceCalculator = new cashInitialBalanceCalculator();

        $graphData = $graphService->getAggregateBalanceFlow($paramsDto);

        $response = [];
        $now = new DateTimeImmutable();

        $balanceFrom = $initialBalanceCalculator->getOnDate($paramsDto, $request->from);
        $balanceTo = $initialBalanceCalculator->getOnDate($paramsDto, $request->to);
        $balanceNow = $initialBalanceCalculator->getOnDate($paramsDto, $now);

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
                'diff' => new cashApiAggregateGetBalanceFlowBalanceDto(
                    '',
                    ($balanceTo[$currency] ?? 0) - ($balanceNow[$currency] ?? 0)
                )
            ];

            $response[] = $dto;
        }

        return $response;
    }
}
