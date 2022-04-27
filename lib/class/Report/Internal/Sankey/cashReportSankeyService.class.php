<?php

final class cashReportSankeyService
{
    /**
     * @var cashModel
     */
    private $model;

    public function __construct()
    {
        $this->model = cash()->getModel();
    }

    public function getDataForPeriod(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array
    {
        $sql = <<<SQL
(SELECT ca.currency, cc.name 'from', ca.name 'to', cc.color color, SUM(ABS(ct.amount)) value, 'income' direction
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type_income
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.category_id, ct.account_id)
UNION ALL
(SELECT ca.currency, ca.name 'from', cc.name 'to', cc.color color, SUM(ABS(ct.amount)) value, 'expense' direction
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type_expense
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.account_id, ct.category_id)
SQL;

        $data = $this->model->query(
            $sql,
            [
                'category_type_income' => cashCategory::TYPE_INCOME,
                'category_type_expense' => cashCategory::TYPE_EXPENSE,
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ]
        )->fetchAll();

        $chartData = array_reduce($data, static function ($carry, $datum) {
            $currency = $datum['currency'];
            if (!isset($carry[$currency])) {
                $carry[$currency] = [
                    'details' => cashCurrencyVO::fromWaCurrency($currency),
                    'data' => [],
                ];
            }

            unset($datum['currency']);
            $datum['value'] = (float) $datum['value'];
            $carry[$currency]['data'][] = $datum;

            return $carry;
        }, []);

        foreach ($chartData as $currency => $datum) {
            array_walk($chartData[$currency]['data'], static function (&$item) use ($datum) {
                $item['currency'] = $datum['details']->getCode();
                $item['currencySign'] = $datum['details']->getSign();
            });

            $income = array_filter($datum['data'], static function (array $line) {
                return $line['direction'] === 'income';
            });
            $expense = array_filter($datum['data'], static function (array $line) {
                return $line['direction'] === 'expense';
            });

            $incomeUnique = array_unique(array_column($income, 'to'));
            foreach ($expense as $item) {
                if (!in_array($item['from'], $incomeUnique, true)) {
                    $chartData[$currency]['data'][] = [
                        'from' => 'stub',
                        'to' => $item['from'],
                        'value' => 0,
                        'direction' => 'income',
                        'color' => '',
                        'currency' => $datum['details']->getCode(),
                        'currencySign' => $datum['details']->getSign(),
                    ];
                }
            }
        }

        return $chartData;
    }
}
