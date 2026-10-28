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

    /**
     * @param int|null $company_id
     * @param int|null $scenario_id
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param string $currency
     * @return array
     * @throws waDbException
     */
    public function getDataForPeriodAndCurrency(?int $company_id, ?int $scenario_id, DateTimeImmutable $from, DateTimeImmutable $to, string $currency): array
    {
        $where = [
            'ct.is_archived = 0',
            'ca.is_archived = 0',
            'ca.is_imaginary != -1',
            'ct.date >= s:date_from AND ct.date <= s:date_to',
            'ct.contractor_contact_id IS NOT NULL',
            'cc.type = s:category_type',
            'ca.currency = s:currency',
        ];
        if ($company_id) {
            array_unshift($where, 'ca.company_id = i:company_id');
        }
        if ($scenario_id) {
            array_unshift($where, 'ca.company_id = i:company_id');
        } else {
            array_unshift($where, 'ct.scenario_id IS NULL');
        }

        return $this->model->query("
            SELECT ct.contractor_contact_id, SUM(ct.amount) total_per_contractor
            FROM cash_transaction ct
            JOIN cash_account ca ON ca.id = ct.account_id
            JOIN cash_category cc ON ct.category_id = cc.id
            WHERE ".implode(' AND ', $where)."
            GROUP BY ct.contractor_contact_id
        ", [
            'company_id' => $company_id,
            'scenario_id' => $scenario_id,
            'category_type' => cashCategory::TYPE_INCOME,
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'currency' => $currency,
        ])->fetchAll('contractor_contact_id', 1);
    }
}
