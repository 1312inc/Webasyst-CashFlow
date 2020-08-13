<?php

/**
 * Class cashReportDdsCategoryDataProvider
 */
class cashReportDdsCategoryDataProvider implements cashReportDdsDataProviderInterface
{
    /**
     * @var cashTransactionModel
     */
    private $transactionModel;

    /**
     * @var cashCategoryRepository
     */
    private $categoryRep;

    /**
     * cashReportDdsCategoryDataProvider constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
        $this->categoryRep = cash()->getEntityRepository(cashCategory::class);
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
select ct.category_id category,
       cc.type category_type,
       ca.currency currency,
       MONTH(ct.date) month,
       sum(ct.amount) per_month
from cash_transaction ct
         join cash_account ca on ct.account_id = ca.id
         join cash_category cc on ct.category_id = cc.id
where date between s:start and s:end
  and ca.is_archived = 0
  and ct.is_archived = 0
group by ct.category_id, ca.currency, MONTH(ct.date)
SQL;

        $data = $this->transactionModel->query(
            $sql,
            [
                'start' => $period->getStart()->format('Y-m-d 00:00:00'),
                'end' => $period->getEnd()->format('Y-m-d 00:00:00'),
            ]
        )->fetchAll();


        $categoryData = [];

        foreach ($data as $datum) {
            if (!isset($categoryData[cashCategory::TYPE_INCOME][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'category' => $datum['category_type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $categoryData[cashCategory::TYPE_INCOME][$groupingDto->key][$datum['currency']] = $initVals;
                    $categoryData[cashCategory::TYPE_EXPENSE][$groupingDto->key][$datum['currency']] = $initVals;
                }
            }

            if (!isset($categoryData[$datum['category']][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $categoryData[$datum['category']][$groupingDto->key][$datum['currency']] = [
                        'category' => $datum['category'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
            }

            $categoryData[$datum['category']][$datum['month']][$datum['currency']]['per_month'] = $datum['per_month'];
            $categoryData[$datum['category_type']][$datum['month']][$datum['currency']]['per_month'] += $datum['per_month'];
        }

        $statData = [];

        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All income'), 0),
            $categoryData[cashCategory::TYPE_INCOME] ?? []
        );
        foreach ($this->categoryRep->findAllIncome() as $category) {
            $statData[] = new cashReportDdsStatDto(
                new cashReportDdsEntity($category->getName(), $category->getId()),
                $categoryData[$category->getId()] ?? []
            );
        }

        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All expense'), 0),
            $categoryData[cashCategory::TYPE_EXPENSE] ?? []
        );
        foreach ($this->categoryRep->findAllExpense() as $category) {
            $statData[] = new cashReportDdsStatDto(
                new cashReportDdsEntity($category->getName(), $category->getId()),
                $categoryData[$category->getId()] ?? []
            );
        }

        return $statData;
    }
}
