<?php

final class cashReportDdsCategoryDataProvider implements cashReportDdsDataProviderInterface
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
     * cashReportDdsServiceCategoryDataProvider constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
        $this->categoryRep = cash()->getEntityRepository(cashCategory::class);
    }

    /**
     * @return cashReportDdsStatDto[]
     * @throws waException
     */
    public function getDataForPeriod(cashReportPeriod $period): array
    {
        $data = $this->transactionModel->query("
            SELECT CONCAT(ct.category_id, '|', if(ct.amount < 0, s:cat_ex, s:cat_in)) id, ca.currency currency, MONTH(ct.date) month, SUM(ct.amount) per_month, ca.is_imaginary
            FROM cash_transaction ct
            JOIN cash_account ca ON ct.account_id = ca.id
            JOIN cash_category cc ON ct.category_id = cc.id
            WHERE ct.date >= s:start AND ct.date < s:end
                AND ca.is_archived = 0
                AND ct.is_archived = 0
            GROUP BY id, ca.currency, MONTH(ct.date), ca.is_imaginary
        ", [
            'cat_ex' => cashCategory::TYPE_EXPENSE,
            'cat_in' => cashCategory::TYPE_INCOME,
            'start'  => $period->getStart()->format('Y-m-d'),
            'end'    => $period->getEnd()->format('Y-m-d'),
        ])->fetchAll();

        $rawData = [];
        $current_month = (int) date('n');
        $categoriesWithChild = cash()->getModel(cashCategory::class)->getChildIdsWithParentIds();

        foreach ($data as $datum) {
            [$id, $type] = explode('|', $datum['id']);

            if (!isset($rawData[cashCategory::TYPE_INCOME][$datum['month']][$datum['currency']])) {
                $this->initTotalAmountPerPeriod($period, $rawData, $datum, $type);
            }

            if (!isset($rawData[$datum['id']][$datum['month']][$datum['currency']])) {
                $this->initAmountPerPeriod($period, $rawData, $datum['id'], $datum['currency'], $id);
            }

            // "Категория": категория + тип
            $this->calculateAmount($rawData[$datum['id']], $datum);

            // просуммируем в родительскую
            if (isset($categoriesWithChild[$id])) {
                $parentIdType = "{$categoriesWithChild[$id]}|{$type}";
                if (!isset($rawData[$parentIdType][$datum['month']][$datum['currency']])) {
                    $this->initAmountPerPeriod($period, $rawData, $parentIdType, $datum['currency'], $id);
                }
                $this->calculateAmount($rawData[$parentIdType], $datum);
            }

            // "Все доходы": просто тип - income/expense
            if (
                0 === (int) $datum['is_imaginary']
                || 1 === (int) $datum['is_imaginary'] && $datum['month'] > $current_month
            ) {
                $this->calculateAmount($rawData[$type], $datum);
            } else {
                $rawData[$type][$datum['month']][$datum['currency']]['imaginary'] = (int) $datum['is_imaginary'];
            }
            $rawData[$type][$datum['month']][$datum['currency']]['max'] = max(
                abs((float) $datum['per_month']),
                abs($rawData[$type][$datum['month']][$datum['currency']]['max'])
            );
        }

        $statData = [];
        $transferCategory = $this->categoryRep->findTransferCategory();

        // incomes
        // total
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All income'),
                cashReportDdsService::ALL_INCOME_KEY,
                false,
                true,
                false,
                '',
                true
            ),
            $rawData[cashCategory::TYPE_INCOME] ?? []
        );

        // expenses
        // total
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All expenses'),
                cashReportDdsService::ALL_EXPENSE_KEY,
                true,
                false,
                false,
                '',
                true
            ),
            $rawData[cashCategory::TYPE_EXPENSE] ?? []
        );

        $saldos = [];
        $keys = array_keys($rawData[cashCategory::TYPE_INCOME] ?? [] + $rawData[cashCategory::TYPE_EXPENSE] ?? []);
        foreach ($keys as $_key) {
            $currency_keys = array_keys($rawData[cashCategory::TYPE_INCOME][$_key] + $rawData[cashCategory::TYPE_EXPENSE][$_key]);
            foreach ($currency_keys as $_currency) {
                $saldos[$_key][$_currency] = [
                    'id' => cashReportDdsService::SALDO_KEY,
                    'max' => 0,
                    'per_month' => ifempty($rawData, cashCategory::TYPE_INCOME, $_key, $_currency, 'per_month', 0) + ifempty($rawData, cashCategory::TYPE_EXPENSE, $_key, $_currency, 'per_month', 0)
                ] + ifempty($rawData, cashCategory::TYPE_INCOME, $_key, $_currency, []) + ifempty($rawData, cashCategory::TYPE_EXPENSE, $_key, $_currency, []);
            }
        }

        // saldo
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('Saldo'),
                cashReportDdsService::SALDO_KEY,
                false,
                false,
                true,
                '',
                true
            ),
            $saldos
        );

        // usual categories
        $category_contact = array_merge(
            $this->categoryRep->findAllIncomeForContact(),
            $this->categoryRep->findAllExpenseForContact()
        );
        foreach ($category_contact as $category) {
            $statData[] = $this->createDdsStatDto($category, $rawData);
        }

        // transfers income
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transferCategory->getName(),
                $transferCategory->getId(),
                false,
                true,
                false,
                sprintf('<i class="icon rounded" style="background-color: %s"></i>', $transferCategory->getColor()),
                false,
                $transferCategory->getColor()
            ),
            $rawData[sprintf('%s|%s', $transferCategory->getId(), cashCategory::TYPE_INCOME)] ?? []
        );

        // transfers expense
        $statData[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transferCategory->getName(),
                $transferCategory->getId(),
                true,
                false,
                false,
                sprintf('<i class="icon rounded" style="background-color: %s"></i>', $transferCategory->getColor()),
                false,
                $transferCategory->getColor()
            ),
            $rawData[sprintf('%s|%s', $transferCategory->getId(), cashCategory::TYPE_EXPENSE)] ?? []
        );

        return $statData ?: [];
    }

    private function calculateAmount(array &$data, array $datum): void
    {
        $data[$datum['month']][$datum['currency']]['per_month'] += (float) $datum['per_month'];
        $data['total'][$datum['currency']]['per_month'] += (float) $datum['per_month'];
        $data['max'][$datum['currency']]['per_month'] = max(
            abs((float) $datum['per_month']),
            abs($data['max'][$datum['currency']]['per_month'])
        );
    }

    private function createDdsStatDto(cashCategory $category, $rawData): cashReportDdsStatDto
    {
        return new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $category->getName(),
                $category->getId(),
                $category->isExpense(),
                $category->isIncome(),
                false,
                $category->getGlyph()
                    ? sprintf('<i class="fas %s" style="color: %s"></i>', $category->getGlyph(), $category->getColor())
                    : sprintf('<i class="icon rounded" style="background-color: %s"></i>', $category->getColor()),
                false,
                $category->getColor(),
                (bool) $category->getCategoryParentId()
            ),
            $rawData[sprintf('%s|%s', $category->getId(), $category->getType())] ?? []
        );
    }

    private function initAmountPerPeriod(cashReportPeriod $period, &$rawData, $datumId, $datumCurrency, $id): void
    {
        foreach ($period->getGrouping() as $groupingDto) {
            $rawData[$datumId][$groupingDto->key][$datumCurrency] = [
                'id' => $id,
                'currency' => $datumCurrency,
                'month' => $groupingDto->key,
                'per_month' => .0,
            ];
        }
        $rawData[$datumId]['total'][$datumCurrency]['per_month'] = .0;
        $rawData[$datumId]['max'][$datumCurrency]['per_month'] = .0;
    }

    private function initTotalAmountPerPeriod(cashReportPeriod $period, &$rawData, $datum, $type): void
    {
        foreach ($period->getGrouping() as $groupingDto) {
            $initVals = [
                'id' => $type,
                'currency' => $datum['currency'],
                'month' => $groupingDto->key,
                'per_month' => .0,
                'max' => .0,
            ];
            $rawData[cashCategory::TYPE_INCOME][$groupingDto->key][$datum['currency']] = $initVals;
            $rawData[cashCategory::TYPE_EXPENSE][$groupingDto->key][$datum['currency']] = $initVals;
        }

        $rawData[cashCategory::TYPE_INCOME]['total'][$datum['currency']]['per_month'] = .0;
        $rawData[cashCategory::TYPE_INCOME]['max'][$datum['currency']]['per_month'] = .0;

        $rawData[cashCategory::TYPE_EXPENSE]['total'][$datum['currency']]['per_month'] = .0;
        $rawData[cashCategory::TYPE_EXPENSE]['max'][$datum['currency']]['per_month'] = .0;
    }
}
