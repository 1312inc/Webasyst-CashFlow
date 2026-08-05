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
     * @param int $company_id
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $to
     * @param string $currency
     * @return array
     * @throws waDbException
     */
    public function getDataForPeriodAndCurrency(int $company_id, DateTimeImmutable $from, DateTimeImmutable $to, string $currency): array
    {
        return $this->model->query("
            SELECT ct.contractor_contact_id, SUM(ct.amount) total_per_contractor
            FROM cash_transaction ct
            JOIN cash_account ca ON ca.id = ct.account_id
            JOIN cash_category cc ON ct.category_id = cc.id
            WHERE ".($company_id ? 'ca.company_id = i:company_id AND ' : '')."ct.is_archived = 0
            AND ca.is_archived = 0
            AND ca.is_imaginary != -1
            AND ct.date >= s:date_from
            AND ct.date <= s:date_to
            AND ct.contractor_contact_id IS NOT NULL
            AND cc.type = s:category_type
            AND ca.currency = s:currency
            GROUP BY ct.contractor_contact_id
        ", [
            'company_id' => $company_id,
            'category_type' => cashCategory::TYPE_INCOME,
            'date_from' => $from->format('Y-m-d'),
            'date_to' => $to->format('Y-m-d'),
            'currency' => $currency,
        ])->fetchAll('contractor_contact_id', 1);
    }
}
