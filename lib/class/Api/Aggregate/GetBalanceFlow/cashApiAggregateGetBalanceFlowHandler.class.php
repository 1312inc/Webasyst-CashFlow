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

            if (isset($balanceFrom[$currency])) {
                $firstDatum = reset($data);
                if (!$firstDatum || $request->from->format('Y-m-d') < $firstDatum['period']) {
                    array_unshift(
                        $data,
                        [
                            'period' => $request->from->format('Y-m-d'),
                            'amount' => $balanceFrom[$currency],
                        ]
                    );
                }

                foreach ($data as $i => $datum) {
                    if ($datum['period'] >= $now->format('Y-m-d')) {
                        // не будем перезаписывать
                        if ($datum['period'] === $now->format('Y-m-d')) {
                            break;
                        }

                        $data = array_merge(
                            array_slice($data, 0, $i),
                            [
                                [
                                    'period' => $now->format('Y-m-d'),
                                    'amount' => $balanceNow[$currency],
                                ],
                            ],
                            array_slice($data, $i, count($data) - $i)
                        );

                        break;
                    }
                }

                $datum = end($data);
                if (isset($datum) && $request->to->format('Y-m-d') > $datum['period']) {
                    $data[] = [
                        'period' => $request->to->format('Y-m-d'),
                        'amount' => $balanceTo[$currency],
                    ];
                }
                reset($data);
            }

            $dto = new cashApiAggregateGetBalanceFlowDto($currency, $data, $balances);

            $response[] = $dto;
        }

        return $response;
    }
}
