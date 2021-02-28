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
            "select concat(ct.category_id, '|', if(ct.amount < 0, '%s', '%s')) id,
           ca.currency currency,
           MONTH(ct.date) month,
           sum(ct.amount) per_month
    from cash_transaction ct
             join cash_account ca on ct.account_id = ca.id
             join cash_category cc on ct.category_id = cc.id
    where ct.date >= s:start and ct.date < s:end
      and ca.is_archived = 0
      and ct.is_archived = 0
    group by id, ca.currency, MONTH(ct.date)",
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
            [$id, $type] = explode('|', $datum['id']);

            if (!isset($rawData[cashCategory::TYPE_INCOME][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $initVals = [
                        'id' => $type,
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                    $rawData[cashCategory::TYPE_INCOME][$groupingDto->key][$datum['currency']] = $initVals;
                    $rawData[cashCategory::TYPE_EXPENSE][$groupingDto->key][$datum['currency']] = $initVals;
                }
                $rawData[cashCategory::TYPE_INCOME]['total'][$datum['currency']]['per_month'] = .0;
                $rawData[cashCategory::TYPE_EXPENSE]['total'][$datum['currency']]['per_month'] = .0;
            }

            if (!isset($rawData[$datum['id']][$datum['month']][$datum['currency']])) {
                foreach ($period->getGrouping() as $groupingDto) {
                    $rawData[$datum['id']][$groupingDto->key][$datum['currency']] = [
                        'id' => $id,
                        'currency' => $datum['currency'],
                        'month' => $groupingDto->key,
                        'per_month' => .0,
                    ];
                }
                $rawData[$datum['id']]['total'][$datum['currency']]['per_month'] = .0;
            }

            $rawData[$datum['id']][$datum['month']][$datum['currency']]['per_month'] = (float) $datum['per_month'];
            $rawData[$datum['id']]['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];

            $rawData[$type][$datum['month']][$datum['currency']]['per_month'] += (float) $datum['per_month'];
            $rawData[$type]['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];
        }

        $statData = [];

        $transferCategory = $this->categoryRep->findTransferCategory();

        // incomes
        // total
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All income'), cashReportDds::ALL_INCOME_KEY, false, true, '', true),
            $rawData[cashCategory::TYPE_INCOME] ?? []
        );
        // usual categories
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
                $rawData[sprintf('%s|%s', $category->getId(), $category->getType())] ?? []
            );
        }
        // transfers
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transferCategory->getName(),
                $transferCategory->getId(),
                false,
                true,
                sprintf('<i class="icon16 color" style="background-color: %s"></i>', $transferCategory->getColor()),
                false,
                $transferCategory->getColor()
            ),
            $rawData[sprintf('%s|%s', $transferCategory->getId(), cashCategory::TYPE_INCOME)] ?? []
        );

        // expenses
        // total
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(_w('All expenses'), cashReportDds::ALL_EXPENSE_KEY, true, false, '', true),
            $rawData[cashCategory::TYPE_EXPENSE] ?? []
        );
        // usual categories
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
                $rawData[sprintf('%s|%s', $category->getId(), $category->getType())] ?? []
            );
        }
        // transfers
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transferCategory->getName(),
                $transferCategory->getId(),
                true,
                false,
                sprintf('<i class="icon16 color" style="background-color: %s"></i>', $transferCategory->getColor()),
                false,
                $transferCategory->getColor()
            ),
            $rawData[sprintf('%s|%s', $transferCategory->getId(), cashCategory::TYPE_EXPENSE)] ?? []
        );


        return $statData ?: [];
    }
}
