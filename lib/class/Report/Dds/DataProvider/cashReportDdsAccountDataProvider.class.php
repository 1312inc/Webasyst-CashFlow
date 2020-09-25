<?php

/**
 * Class cashReportDdsAccountDataProvider
 */
class cashReportDdsAccountDataProvider implements cashReportDdsDataProviderInterface
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
     * cashReportDdsAccountDataProvider constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
        $this->accountRep = cash()->getEntityRepository(cashAccount::class);
    }

    /**
     * @param cashReportDdsPeriod $period
     *
     * @return cashReportDdsStatDto[]
     * @throws waException
     */
    public function getDataForPeriod(cashReportDdsPeriod $period): array
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
            where ct.date between s:start and s:end
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

            if (!isset($rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$month][$currency])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'account' => $catType,
                        'type' => $catType,
                        'currency' => $currency,
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$groupingDto->key][$currency] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY][$groupingDto->key][$currency] = $initVals;
                }
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
            }

            $rawData[$catType][$account][$month][$currency]['per_month'] = (float) $perMonth;
            if ($catType === cashCategory::TYPE_INCOME) {
                $categoryTypeKey = cashReportDds::ALL_INCOME_KEY;
            } else {
                $categoryTypeKey = cashReportDds::ALL_EXPENSE_KEY;
            }
            $rawData[$catType][$categoryTypeKey][$month][$currency]['per_month'] += (float) $perMonth;
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

        $statDataIncome[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All income'), cashReportDds::ALL_INCOME_KEY, false, true, '', true),
            $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY] ?? []
        );
        $statDataExpense[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All expenses'), cashReportDds::ALL_EXPENSE_KEY, true, false, '', true
            ),
            $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY] ?? []
        );

        foreach ($this->accountRep->findAllActiveForContact() as $account) {
            $data = $rawData[cashCategory::TYPE_INCOME][$account->getId()] ?? [];
            if ($data) {
                $statDataIncome[] = new cashReportDdsStatDto(
                    new cashReportDdsEntity(
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
                $statDataExpense[] = new cashReportDdsStatDto(
                    new cashReportDdsEntity(
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
