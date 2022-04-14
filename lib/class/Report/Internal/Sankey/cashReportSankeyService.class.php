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

    public function getDataForYear(DateTimeImmutable $year): array
    {
        $sql = <<<SQL
(SELECT ca.currency, cc.name 'from', ca.name 'to', cc.color color, SUM(ABS(ct.amount)) value
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
(SELECT ca.currency, ca.name 'from', cc.name 'to', cc.color color, SUM(ABS(ct.amount)) value
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

        $data = $this->model->query($sql,
            [
                'category_type_income' => cashCategory::TYPE_INCOME,
                'category_type_expense' => cashCategory::TYPE_EXPENSE,
                'date_from' => sprintf('%d-01-01', $year->format('Y')),
                'date_to' => sprintf('%d-12-31', $year->format('Y')),
            ]
        )->fetchAll();

        return array_reduce($data, static function ($carry, $datum) {
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
    }
}
