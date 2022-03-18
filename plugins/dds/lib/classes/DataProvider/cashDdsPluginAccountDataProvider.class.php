<?php

final class cashDdsPluginAccountDataProvider implements cashDdsPluginDataProviderInterface
{
    /**
     * @var cashTransactionModel
     */
    private $transactionModel;

    /**
     * @var cashAccountRepository
     */
    private $accountRep;

    /**
     * cashDdsPluginAccountDataProvider constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
        $this->accountRep = cash()->getEntityRepository(cashAccount::class);
    }

    /**
     * @param cashDdsPluginPeriod $period
     *
     * @return cashDdsPluginStatDto[]
     * @throws waException
     */
    public function getDataForPeriod(cashDdsPluginPeriod $period): array
    {
        $sql = sprintf(
            "select ct.account_id account,
                   if(ct.amount < 0, '%s', '%s') category_type,
                   ca.currency currency,
                   MONTH(ct.date) month,
                   sum(ct.amount) per_month
            from cash_transaction ct
                     join cash_account ca on ct.account_id = ca.id
                     join cash_category cc on ct.category_id = cc.id
            where ct.date >= s:start and ct.date < s:end
              and ca.is_archived = 0
              and ct.is_archived = 0
            group by cc.type, ct.account_id, ca.currency, MONTH(ct.date)",
            cashCategory::TYPE_EXPENSE,
            cashCategory::TYPE_INCOME
        );

        $data = $this->transactionModel->query(
            $sql,
            [
                'start' => $period->getStart()->format('Y-m-d'),
                'end' => $period->getEnd()->format('Y-m-d'),
            ]
        )->fetchAll();

        $rawData = [];

        foreach ($data as $datum) {
            $month = $datum['month'];
            $currency = $datum['currency'];
            $catType = $datum['category_type'];
            $account = $datum['account'];
            $perMonth = $datum['per_month'];

            if (!isset($rawData[cashCategory::TYPE_INCOME][cashDdsPlugin::ALL_INCOME_KEY][$month][$currency])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'account' => $catType,
                        'type' => $catType,
                        'currency' => $currency,
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][cashDdsPlugin::ALL_INCOME_KEY][$groupingDto->key][$currency] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][cashDdsPlugin::ALL_EXPENSE_KEY][$groupingDto->key][$currency] = $initVals;
                }
                $rawData[cashCategory::TYPE_INCOME][cashDdsPlugin::ALL_INCOME_KEY]['total'][$currency]['per_month'] = .0;
                $rawData[cashCategory::TYPE_EXPENSE][cashDdsPlugin::ALL_EXPENSE_KEY]['total'][$currency]['per_month'] = .0;
            }

            if (!isset($rawData[$catType][$account][$month][$currency])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$catType][$account][$groupingDto->key][$currency] = [
                        'account' => $account,
                        'type' => $catType,
                        'currency' => $currency,
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
                $rawData[$catType][$account]['total'][$currency]['per_month'] = .0;
            }

            $rawData[$catType][$account][$month][$currency]['per_month'] = (float) $perMonth;
            $rawData[$catType][$account]['total'][$currency]['per_month'] += (float) $perMonth;

            if ($catType === cashCategory::TYPE_INCOME) {
                $categoryTypeKey = cashDdsPlugin::ALL_INCOME_KEY;
            } else {
                $categoryTypeKey = cashDdsPlugin::ALL_EXPENSE_KEY;
            }
            $rawData[$catType][$categoryTypeKey][$month][$currency]['per_month'] += (float) $perMonth;
            $rawData[$catType][$categoryTypeKey]['total'][$currency]['per_month'] += (float) $perMonth;
        }

        $statDataIncome = $statDataExpense = [];


//        $accounts = array_reduce(
//            $this->accountRep->findAllActive(),
//            function ($accounts, cashAccount $account) {
//                $accounts[$account->getId()] = $account;
//
//                return $accounts;
//            },
//            []
//        );

        $statDataIncome[] = new cashDdsPluginStatDto(
            new cashDdsPluginEntity(_w('All income'), cashDdsPlugin::ALL_INCOME_KEY, false, true, '', true),
            $rawData[cashCategory::TYPE_INCOME][cashDdsPlugin::ALL_INCOME_KEY] ?? []
        );
        $statDataExpense[] = new cashDdsPluginStatDto(
            new cashDdsPluginEntity(
                _w('All expenses'), cashDdsPlugin::ALL_EXPENSE_KEY, true, false, '', true
            ),
            $rawData[cashCategory::TYPE_EXPENSE][cashDdsPlugin::ALL_EXPENSE_KEY] ?? []
        );

        foreach ($this->accountRep->findAllActiveForContact() as $account) {
            $data = $rawData[cashCategory::TYPE_INCOME][$account->getId()] ?? [];
            if ($data) {
                $statDataIncome[] = new cashDdsPluginStatDto(
                    new cashDdsPluginEntity(
                        $account->getName(),
                        $account->getId(),
                        false,
                        true,
                        $account->getIconHtml()
                    ),
                    $data
                );
            }
            $data = $rawData[cashCategory::TYPE_EXPENSE][$account->getId()] ?? [];
            if ($data) {
                $statDataExpense[] = new cashDdsPluginStatDto(
                    new cashDdsPluginEntity(
                        $account->getName(),
                        $account->getId(),
                        true,
                        false,
                        $account->getIconHtml()
                    ),
                    $data
                );
            }
        }

        return array_merge($statDataIncome, $statDataExpense) ?: [];
    }
}
