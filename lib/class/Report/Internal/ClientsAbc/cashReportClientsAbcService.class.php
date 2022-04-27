<?php

final class cashReportClientsAbcService
{
    /**
     * @var cashModel
     */
    private $model;

    public function __construct()
    {
        $this->model = cash()->getModel();
    }

    public function getDataForPeriodAndCurrency(DateTimeImmutable $from, DateTimeImmutable $to, string $currency): array
    {
        $incomeSql = <<<SQL
SELECT ct.contractor_contact_id, SUM(ct.amount) total_per_contractor
FROM cash_transaction ct
         JOIN cash_account ca on ca.id = ct.account_id
         JOIN cash_category cc on ct.category_id = cc.id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
  AND ct.contractor_contact_id IS NOT NULL
  AND cc.type = s:category_type
  AND ca.currency = s:currency
GROUP BY ct.contractor_contact_id
SQL;

        $data = $this->model->query($incomeSql,
            [
                'category_type' => cashCategory::TYPE_INCOME,
                'date_from' => $from->format('Y-m-d'),
                'date_to' => $to->format('Y-m-d'),
                'currency' => $currency,
            ]
        )->fetchAll('contractor_contact_id', 1);

        return $data;
    }
}
