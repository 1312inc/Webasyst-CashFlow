<?php

final class cashPlanService
{
    /**
     * @var cashTransactionModel
     */
    private $transaction_model;

    /**
     * @var cashCategoryRepository
     */
    private $category_rep;

    /**
     * @var cashReportPeriod
     */
    private $period;

    /**
     * @param int $year
     * @throws waException
     */
    public function __construct(int $year)
    {
        $start = DateTimeImmutable::createFromFormat('Y-m-d|', date($year.'-01-01'));

        $this->period = new cashReportPeriod(
            $year,
            $start->format('Y'),
            $start,
            $start->modify('next year'),
            cashReportPeriod::GROUPING_MONTH
        );
        $this->transaction_model = cash()->getModel(cashTransaction::class);
        $this->category_rep = cash()->getEntityRepository(cashCategory::class);
    }

    public function getPeriod(): cashReportPeriod
    {
        return $this->period;
    }

    /**
     * @return cashReportPeriod[]
     * @throws waException
     */
    public function getPeriodsByYear(): array
    {
        $periods = [];

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        foreach ($model->getYearsWithTransactions() as $years_with_transaction) {
            if ($years_with_transaction > 1900) {
                $periods[] = cashReportPeriod::createForYear($years_with_transaction);
            }
        }

        return $periods;
    }

    /**
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getDataForPeriod(): array
    {
        $data = $this->transaction_model->query("
            SELECT CONCAT(ct.category_id, '|', if(ct.amount < 0, s:cat_ex, s:cat_in)) id, ca.currency currency, MONTH(ct.date) month, SUM(ct.amount) per_month, ca.is_imaginary
            FROM cash_transaction ct
            JOIN cash_account ca ON ct.account_id = ca.id
            JOIN cash_category cc ON ct.category_id = cc.id
            WHERE ct.date >= s:start AND ct.date < s:end
                AND ct.category_id <> -1312
                AND ca.is_archived = 0
                AND ct.is_archived = 0
            GROUP BY id, ca.currency, MONTH(ct.date), ca.is_imaginary
        ", [
            'cat_ex' => cashCategory::TYPE_EXPENSE,
            'cat_in' => cashCategory::TYPE_INCOME,
            'start'  => $this->period->getStart()->format('Y-m-d'),
            'end'    => $this->period->getEnd()->format('Y-m-d'),
        ])->fetchAll();

        $raw_data = [];
        $current_month = (int) date('n');
        $categories_with_child = cash()->getModel(cashCategory::class)->getChildIdsWithParentIds();

        foreach ($data as $datum) {
            [$id, $type] = explode('|', $datum['id']);

            if (!isset($raw_data[cashCategory::TYPE_INCOME][$datum['month']][$datum['currency']])) {
                $this->initTotalAmountPerPeriod($raw_data, $datum, $type);
            }

            if (!isset($raw_data[$datum['id']][$datum['month']][$datum['currency']])) {
                $this->initAmountPerPeriod($raw_data, $datum['id'], $datum['currency'], $id);
            }

            // "Категория": категория + тип
            $this->calculateAmount($raw_data[$datum['id']], $datum);

            // просуммируем в родительскую
            if (isset($categories_with_child[$id])) {
                $parent_id_type = "{$categories_with_child[$id]}|{$type}";
                if (!isset($raw_data[$parent_id_type][$datum['month']][$datum['currency']])) {
                    $this->initAmountPerPeriod($raw_data, $parent_id_type, $datum['currency'], $id);
                }
                $this->calculateAmount($raw_data[$parent_id_type], $datum);
            }

            // "Все доходы": просто тип - income/expense
            if (
                0 === (int)$datum['is_imaginary']
                || 1 === (int)$datum['is_imaginary'] && $datum['month'] > $current_month
            ) {
                $this->calculateAmount($raw_data[$type], $datum);
            } else {
                $raw_data[$type][$datum['month']][$datum['currency']]['imaginary'] = (int)$datum['is_imaginary'];
            }
            $raw_data[$type][$datum['month']][$datum['currency']]['max'] = max(
                abs((float)$datum['per_month']),
                abs($raw_data[$type][$datum['month']][$datum['currency']]['max'])
            );
        }

        $stat_data = [];
        $transfer_category = $this->category_rep->findTransferCategory();

        // incomes
        // total
        $stat_data[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All income'),
                cashReportDdsService::ALL_INCOME_KEY,
                false,
                true,
                false,
                '',
                true
            ),
            $raw_data[cashCategory::TYPE_INCOME] ?? []
        );

        // expenses
        // total
        $stat_data[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                _w('All expenses'),
                cashReportDdsService::ALL_EXPENSE_KEY,
                true,
                false,
                false,
                '',
                true
            ),
            $raw_data[cashCategory::TYPE_EXPENSE] ?? []
        );

        $saldos = [];
        $keys = array_keys(ifempty($raw_data, cashCategory::TYPE_INCOME, []) + ifempty($raw_data, cashCategory::TYPE_EXPENSE, []));
        foreach ($keys as $_key) {
            $currency_keys = array_keys($raw_data[cashCategory::TYPE_INCOME][$_key] + $raw_data[cashCategory::TYPE_EXPENSE][$_key]);
            foreach ($currency_keys as $_currency) {
                $saldos[$_key][$_currency] = [
                        'id' => cashReportDdsService::SALDO_KEY,
                        'max' => 0,
                        'per_month' => ifempty($raw_data, cashCategory::TYPE_INCOME, $_key, $_currency, 'per_month', 0) + ifempty($raw_data, cashCategory::TYPE_EXPENSE, $_key, $_currency, 'per_month', 0)
                    ] + ifempty($raw_data, cashCategory::TYPE_INCOME, $_key, $_currency, []) + ifempty($raw_data, cashCategory::TYPE_EXPENSE, $_key, $_currency, []);
            }
        }

        // saldo
        $stat_data[] = new cashReportDdsStatDto(
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
            $this->category_rep->findAllIncomeForContact(),
            $this->category_rep->findAllExpenseForContact()
        );

        $childs_cat = [];
        /** @var cashCategory $category */
        foreach ($category_contact as $category) {
            $parent_id = $category->getCategoryParentId();
            if ($parent_id) {
                $childs_cat[$parent_id][] = $this->createDdsStatDto($category, $raw_data);
            }
        }
        /** @var cashCategory $category */
        foreach ($category_contact as $category) {
            if ($category->getCategoryParentId() === null) {
                $stat_data[] = $this->createDdsStatDto($category, $raw_data);
                if (isset($childs_cat[$category->getId()])) {
                    $stat_data = array_merge($stat_data, $childs_cat[$category->getId()]);
                }
            }
        }

        // transfers income
        $stat_data[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transfer_category->getName(),
                $transfer_category->getId(),
                false,
                true,
                false,
                sprintf('<i class="icon rounded" style="background-color: %s"></i>', $transfer_category->getColor()),
                false,
                $transfer_category->getColor()
            ),
            $raw_data[sprintf('%s|%s', $transfer_category->getId(), cashCategory::TYPE_INCOME)] ?? []
        );

        // transfers expense
        $stat_data[] = new cashReportDdsStatDto(
            new cashReportDdsEntity(
                $transfer_category->getName(),
                $transfer_category->getId(),
                true,
                false,
                false,
                sprintf('<i class="icon rounded" style="background-color: %s"></i>', $transfer_category->getColor()),
                false,
                $transfer_category->getColor()
            ),
            $raw_data[sprintf('%s|%s', $transfer_category->getId(), cashCategory::TYPE_EXPENSE)] ?? []
        );

        return $stat_data ?: [];
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

    private function createDdsStatDto(cashCategory $category, $raw_data): cashReportDdsStatDto
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
            $raw_data[sprintf('%s|%s', $category->getId(), $category->getType())] ?? []
        );
    }

    private function initAmountPerPeriod(&$raw_data, $datum_id, $datum_currency, $id): void
    {
        foreach ($this->period->getGrouping() as $grouping_dto) {
            $raw_data[$datum_id][$grouping_dto->key][$datum_currency] = [
                'id' => $id,
                'currency' => $datum_currency,
                'month' => $grouping_dto->key,
                'per_month' => .0,
            ];
        }
        $raw_data[$datum_id]['total'][$datum_currency]['per_month'] = .0;
        $raw_data[$datum_id]['max'][$datum_currency]['per_month'] = .0;
    }

    private function initTotalAmountPerPeriod(&$raw_data, $datum, $type): void
    {
        foreach ($this->period->getGrouping() as $grouping_dto) {
            $init_vals = [
                'id' => $type,
                'currency' => $datum['currency'],
                'month' => $grouping_dto->key,
                'per_month' => .0,
                'max' => .0,
            ];
            $raw_data[cashCategory::TYPE_INCOME][$grouping_dto->key][$datum['currency']] = $init_vals;
            $raw_data[cashCategory::TYPE_EXPENSE][$grouping_dto->key][$datum['currency']] = $init_vals;
        }

        $raw_data[cashCategory::TYPE_INCOME]['total'][$datum['currency']]['per_month'] = .0;
        $raw_data[cashCategory::TYPE_INCOME]['max'][$datum['currency']]['per_month'] = .0;

        $raw_data[cashCategory::TYPE_EXPENSE]['total'][$datum['currency']]['per_month'] = .0;
        $raw_data[cashCategory::TYPE_EXPENSE]['max'][$datum['currency']]['per_month'] = .0;
    }
}
