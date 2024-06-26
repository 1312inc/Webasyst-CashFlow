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
            $request->getFrom(),
            $request->getTo(),
            $request->getGroupBy(),
            cashAggregateFilter::createFromHash($request->getFilter())
        );

        $graphService = new cashGraphService();
        $initialBalanceCalculator = new cashInitialBalanceCalculator();

        $data = $graphService->getAggregateChartData($paramsDto);

        $firstDayPrepend = null;
        switch (true) {
            case $paramsDto->filter->isFilterByAccount():
                $account = cash()->getEntityRepository(cashAccount::class)
                    ->findByIdForContact($paramsDto->filter->getAccountId());

                if ($account
                    && cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account->getId())
                ) {
                    $accountFirstTransaction = cash()->getEntityRepository(cashTransaction::class)
                        ->findFirstForAccount($account);
                    if ($accountFirstTransaction) {
                        $accountCreateDate = new DateTimeImmutable($accountFirstTransaction->getDate());
                        $dateFormat = $graphService->getGroupingDateFormat($paramsDto);

                        // в случае наличия баланса до запрашиваемого промежутка ?from
                        if ($paramsDto->from->format($dateFormat) > $accountCreateDate->format($dateFormat)) {
                            $firstDayPrepend = [
                                'groupkey' => $paramsDto->from->format($dateFormat),
                                'currency' => $account->getCurrency(),
                                'balance' => $initialBalanceCalculator->getOnDateForAccount(
                                    $account,
                                    $paramsDto->from,
                                    wa()->getUser()
                                ),
                                'incomeAmount' => 0.0,
                                'expenseAmount' => 0.0,
                                'profitAmount' => 0.0,
                            ];
                        }
                    }
                }

                break;

            case $paramsDto->filter->isFilterByCurrency():
                $currency = cashCurrencyVO::fromWaCurrency($paramsDto->filter->getCurrency());
                $currencyFirstTransaction = cash()->getEntityRepository(cashTransaction::class)
                    ->findFirstForCurrency($currency);
                if ($currencyFirstTransaction) {
                    $accountCreateDate = new DateTimeImmutable($currencyFirstTransaction->getDate());
                    $dateFormat = $graphService->getGroupingDateFormat($paramsDto);

                    // в случае наличия баланса до запрашиваемого промежутка ?from
                    if ($paramsDto->from->format($dateFormat) > $accountCreateDate->format($dateFormat)) {
                        $balance = $initialBalanceCalculator->getOnDateForCurrency(
                            $currency,
                            $paramsDto->from,
                            wa()->getUser()
                        );
                        if ($balance) {
                            $firstDayPrepend = [
                                'groupkey' => $paramsDto->from->format($dateFormat),
                                'currency' => $currency->getCode(),
                                'balance' => $balance,
                                'incomeAmount' => 0.0,
                                'expenseAmount' => 0.0,
                                'profitAmount' => 0.0,
                            ];
                        }
                    }
                }
                break;
        }

        if ($firstDayPrepend) {
            array_unshift($data, $firstDayPrepend);
        }

        return $data;
    }
}
