<?php

/**
 * Class cashReportDdsContractorDataProvider
 */
class cashReportDdsContractorDataProvider implements cashReportDdsDataProviderInterface
{
    /**
     * @var cashTransactionModel
     */
    private $transactionModel;

    /**
     * cashReportDdsAccountDataProvider constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
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
select ct.contractor_contact_id id,
       cc.type type,
       ca.currency currency,
       MONTH(ct.date) month,
       sum(ct.amount) per_month
from cash_transaction ct
         join cash_account ca on ct.account_id = ca.id
         join cash_category cc on ct.category_id = cc.id
where ct.date between s:start and s:end
  and ca.is_archived = 0
  and ct.is_archived = 0
  and ct.contractor_contact_id is not null
group by cc.type, ct.contractor_contact_id, ca.currency, MONTH(ct.date)
SQL;

        $data = $this->transactionModel->query(
            $sql,
            [
                'start' => $period->getStart()->format('Y-m-d'),
                'end' => $period->getEnd()->format('Y-m-d'),
            ]
        )->fetchAll();

        $rawData = [];

        $incomeExists = $expenseExists = false;
        foreach ($data as $datum) {
            if (!isset($rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'id' => $datum['type'],
                        'type' => $datum['type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY][$groupingDto->key][$datum['currency']] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY][$groupingDto->key][$datum['currency']] = $initVals;
                }
                $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY]['total'][$datum['currency']]['per_month'] = .0;
                $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY]['total'][$datum['currency']]['per_month'] = .0;
            }

            if (!isset($rawData[$datum['type']][$datum['id']][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$datum['type']][$datum['id']][$groupingDto->key][$datum['currency']] = [
                        'id' => $datum['id'],
                        'type' => $datum['type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
                $rawData[$datum['type']][$datum['id']]['total'][$datum['currency']]['per_month'] = .0;
            }

            $rawData[$datum['type']][$datum['id']][$datum['month']][$datum['currency']]['per_month'] = (float) $datum['per_month'];
            $rawData[$datum['type']][$datum['id']]['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];

            if ($datum['type'] === cashCategory::TYPE_INCOME) {
                $incomeExists = $incomeExists || abs($datum['per_month']) > 0;
                $rawData[$datum['type']][cashReportDds::ALL_INCOME_KEY][$datum['month']][$datum['currency']]['per_month'] += (float) $datum['per_month'];
                $rawData[$datum['type']][cashReportDds::ALL_INCOME_KEY]['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];
            } else {
                $expenseExists = $expenseExists || abs($datum['per_month']) > 0;
                $rawData[$datum['type']][cashReportDds::ALL_EXPENSE_KEY][$datum['month']][$datum['currency']]['per_month'] += (float) $datum['per_month'];
                $rawData[$datum['type']][cashReportDds::ALL_EXPENSE_KEY]['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];
            }
        }

//        $accounts = array_reduce(
//            $this->accountRep->findAllActive(),
//            function ($accounts, cashAccount $account) {
//                $accounts[$account->getId()] = $account;
//
//                return $accounts;
//            },
//            []
//        );

        $statData = [];
        if ($expenseExists) {
            $statData[cashCategory::TYPE_EXPENSE] = [
                new cashReportDdsStatDto(
                    new cashReportDdsEntity(_w('All expenses'), cashReportDds::ALL_EXPENSE_KEY, true, false, '', true),
                    $rawData[cashCategory::TYPE_EXPENSE][cashReportDds::ALL_EXPENSE_KEY] ?? []
                ),
            ];
        }
        if ($incomeExists) {
            $statData[cashCategory::TYPE_INCOME] = [
                new cashReportDdsStatDto(
                    new cashReportDdsEntity(_w('All income'), cashReportDds::ALL_INCOME_KEY, false, true, '', true),
                    $rawData[cashCategory::TYPE_INCOME][cashReportDds::ALL_INCOME_KEY] ?? []
                ),
            ];
        }

        foreach ($rawData as $type => $rawDatum) {
            foreach ($rawDatum as $id => $datum) {
                if (in_array($id, [cashReportDds::ALL_EXPENSE_KEY, cashReportDds::ALL_INCOME_KEY], true)) {
                    continue;
                }

                $contact = new waContact($id);
                if ($contact->exists()) {
                    $entity = new cashReportDdsEntity(
                        $contact->getName(),
                        $contact->getId(),
                        $type === cashCategory::TYPE_EXPENSE,
                        $type === cashCategory::TYPE_INCOME,
                        sprintf(
                            '<i class="icon16 userpic20"  style="background-image: url(%s);"></i>',
                            $contact->getPhoto(20)
                        ),
                        false
                    );
                } else {
                    $entity = new cashReportDdsEntity(
                        sprintf('Deleted contact #%d', $id),
                        $id,
                        $type === cashCategory::TYPE_EXPENSE,
                        $type === cashCategory::TYPE_INCOME,
                        sprintf(
                            '<i class="icon16 userpic20"  style="background-image: url(%s);"></i>',
                            wa()->getRootUrl() . 'wa-content/img/userpic20.jpg'
                        ),
                        false
                    );
                }
                $statData[$type][] = new cashReportDdsStatDto($entity, $datum);
            }
        }

        return array_merge(
            $statData[cashCategory::TYPE_INCOME] ?? [],
            $statData[cashCategory::TYPE_EXPENSE] ?? []
        ) ?: [];
    }
}
