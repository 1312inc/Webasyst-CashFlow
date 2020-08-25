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
        $sql = <<<SQL
select ct.account_id account,
       cc.type category_type,
       ca.currency currency,
       MONTH(ct.date) month,
       sum(ct.amount) per_month
from cash_transaction ct
         join cash_account ca on ct.account_id = ca.id
         join cash_category cc on ct.category_id = cc.id
where ct.date between s:start and s:end
  and ca.is_archived = 0
  and ct.is_archived = 0
group by cc.type, ct.account_id, ca.currency, MONTH(ct.date)
SQL;

        $data = $this->transactionModel->query(
            $sql,
            [
                'start' => $period->getStart()->format('Y-m-d'),
                'end' => $period->getEnd()->format('Y-m-d'),
            ]
        )->fetchAll();

        $rawData = [];

        foreach ($data as $datum) {
            if (!isset($rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'account' => $datum['category_type'],
                        'type' => $datum['category_type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$groupingDto->key][$datum['currency']] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY][$groupingDto->key][$datum['currency']] = $initVals;
                }
            }

            if (!isset($rawData[$datum['category_type']][$datum['account']][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$datum['category_type']][$datum['account']][$groupingDto->key][$datum['currency']] = [
                        'account' => $datum['account'],
                        'type' => $datum['category_type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
            }

            $rawData[$datum['category_type']][$datum['account']][$datum['month']][$datum['currency']]['per_month'] = (float)$datum['per_month'];
            if ($datum['category_type'] === cashCategory::TYPE_INCOME) {
                $rawData[$datum['category_type']][cashReportDds::ALL_INCOME_KEY][$datum['month']][$datum['currency']]['per_month'] += (float)$datum['per_month'];
            } else {
                $rawData[$datum['category_type']][cashReportDds::ALL_EXPENSE_KEY][$datum['month']][$datum['currency']]['per_month'] += (float)$datum['per_month'];
            }
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
                _w('All expense'), cashReportDds::ALL_EXPENSE_KEY, true, false, '', true
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
