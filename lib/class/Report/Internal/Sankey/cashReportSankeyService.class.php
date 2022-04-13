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
        $incomeSql = <<<SQL
SELECT cc.name category_name, ca.name account_name, SUM(ct.amount) sankey_amount
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.category_id, ct.account_id
SQL;

        $expenseSql = <<<SQL
SELECT ca.name account_name, cc.name category_name, SUM(ct.amount) sankey_amount
FROM cash_transaction ct
         JOIN cash_category cc on ct.category_id = cc.id
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND cc.type = s:category_type
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
GROUP BY ct.account_id, ct.category_id
SQL;

        $incomeData = $this->model->query($incomeSql,
            [
                'category_type' => cashCategory::TYPE_INCOME,
                'date_from' => sprintf('%d-01-01', $year->format('Y')),
                'date_to' => sprintf('%d-12-31', $year->format('Y')),
            ]
        )->fetchAll();

        $expenseData = $this->model->query($expenseSql,
            [
                'category_type' => cashCategory::TYPE_EXPENSE,
                'date_from' => sprintf('%d-01-01', $year->format('Y')),
                'date_to' => sprintf('%d-12-31', $year->format('Y')),
            ]
        )->fetchAll();

        $data = [];
        foreach ($incomeData as $incomeDatum) {
            $data[] = [
                'from' => $incomeDatum['category_name'],
                'to' => $incomeDatum['account_name'],
                'value' => (float) $incomeDatum['sankey_amount'],
            ];
        }
        foreach ($expenseData as $expenseDatum) {
            $data[] = [
                'from' => $expenseDatum['account_name'],
                'to' => $expenseDatum['category_name'],
                'value' => (float) $expenseDatum['sankey_amount'],
            ];
        }

        return $data;
    }
}
