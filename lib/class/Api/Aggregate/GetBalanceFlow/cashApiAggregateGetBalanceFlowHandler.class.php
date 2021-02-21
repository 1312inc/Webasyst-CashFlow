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
            $balances = [
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
                ),
            ];

            $dataResult = array_combine(array_column($data, 'period'), $data);

            if (!isset($dataResult[$request->from->format('Y-m-d')])) {
                $dataResult[$request->from->format('Y-m-d')] = [
                    'period' => $request->from->format('Y-m-d'),
                    'amount' => $balances['from']->amount,
                ];
            }

            if ($now > $request->from && $now < $request->to
                && !isset($dataResult[$now->format('Y-m-d')])
            ) {
                $dataResult[$now->format('Y-m-d')] = [
                    'period' => $now->format('Y-m-d'),
                    'amount' => $balances['now']->amount,
                ];
            }

            if (!isset($dataResult[$request->to->format('Y-m-d')])) {
                $dataResult[$request->to->format('Y-m-d')] = [
                    'period' => $request->to->format('Y-m-d'),
                    'amount' => $balances['to']->amount,
                ];
            }

            ksort($dataResult);

            $dto = new cashApiAggregateGetBalanceFlowDto($currency, array_values($dataResult), $balances);

            $response[] = $dto;
        }

        return $response;
    }
}
