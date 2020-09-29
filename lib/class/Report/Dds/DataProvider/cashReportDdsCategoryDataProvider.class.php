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
        $sql = sprintf(
            "select ct.category_id id,
           if (cc.type < 0, '%s', '%s') type,
           ca.currency currency,
           MONTH(ct.date) month,
           sum(ct.amount) per_month
    from cash_transaction ct
             join cash_account ca on ct.account_id = ca.id
             join cash_category cc on ct.category_id = cc.id
    where ct.date between s:start and s:end
      and ca.is_archived = 0
      and ct.is_archived = 0
    group by ct.category_id, ca.currency, MONTH(ct.date)",
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
            if (!isset($rawData[cashCategory::TYPE_INCOME][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'id' => $datum['type'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][$groupingDto->key][$datum['currency']] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][$groupingDto->key][$datum['currency']] = $initVals;
                }
            }

            if (!isset($rawData[$datum['id']][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$datum['id']][$groupingDto->key][$datum['currency']] = [
                        'id' => $datum['id'],
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
            }

            $rawData[$datum['id']][$datum['month']][$datum['currency']]['per_month'] = (float) $datum['per_month'];
            $rawData[$datum['type']][$datum['month']][$datum['currency']]['per_month'] += (float) $datum['per_month'];
        }

        $statData = [];

        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All income'), cashReportDds::ALL_INCOME_KEY, false, true, '', true),
            $rawData[cashCategory::TYPE_INCOME] ?? []
        );
        foreach ($this->categoryRep->findAllIncomeForContact() as $category) {
            $statData[] = new cashReportDdsStatDto(
                new cashReportDdsEntity(
                    $category->getName(),
                    $category->getId(),
                    $category->isExpense(),
                    $category->isIncome(),
                    sprintf('<i class="icon16 color" style="background-color: %s"></i>', $category->getColor()),
                    false,
                    $category->getColor()
                ),
                $rawData[$category->getId()] ?? []
            );
        }

        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All expenses'), cashReportDds::ALL_EXPENSE_KEY, true, false, '', true),
            $rawData[cashCategory::TYPE_EXPENSE] ?? []
        );
        foreach ($this->categoryRep->findAllExpense() as $category) {
            $statData[] = new cashReportDdsStatDto(
                new cashReportDdsEntity(
                    $category->getName(),
                    $category->getId(),
                    $category->isExpense(),
                    $category->isIncome(),
                    sprintf('<i class="icon16 color" style="background-color: %s"></i>', $category->getColor()),
                    false,
                    $category->getColor()
                ),
                $rawData[$category->getId()] ?? []
            );
        }

        return $statData ?: [];
    }
}
