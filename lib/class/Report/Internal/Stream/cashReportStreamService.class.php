<?php

final class cashReportStreamService
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
     * @param DateTimeImmutable $dateFrom
     * @param DateTimeImmutable $dateTo
     * @return array
     * @throws waDbException
     */
    public function getDataForPeriod(?int $company_id, ?int $scenario_id, DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array
    {
        $where = [
            'ct.is_archived = 0',
            'ca.is_archived = 0',
            'ca.is_imaginary != -1',
            'ct.date >= s:date_from AND ct.date <= s:date_to',
            'ct.category_id != s:transfer_id'

        ];
        if ($company_id) {
            array_unshift($where, 'ca.company_id = i:company_id');
        }
        if ($scenario_id) {
            array_unshift($where, 'ca.company_id = i:company_id');
        } else {
            array_unshift($where, 'ct.scenario_id IS NULL');
        }

        $_where = implode(' AND ', $where);
        $grouping = new cashReportSankeyDataGrouping($dateFrom, $dateTo);
        $currencies = $this->model->query("
            SELECT ca.currency
            FROM cash_transaction ct
            JOIN cash_account ca on ca.id = ct.account_id
            WHERE $_where
            GROUP BY ca.currency
        ", [
            'company_id' => $company_id,
            'scenario_id' => $scenario_id,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        ])->fetchAll('currency');

        $currencies = array_column($currencies, 'currency');
        $categories = $this->model->query("
            SELECT cc.*
            FROM cash_transaction ct
            JOIN cash_account ca on ca.id = ct.account_id
            JOIN cash_category cc on cc.id = ct.category_id
            WHERE $_where
            GROUP BY ct.category_id
        ", [
            'company_id' => $company_id,
            'scenario_id' => $scenario_id,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        ])->fetchAll('id');

        $data = $this->model->query("
            SELECT ca.currency, {$grouping->getSqlGroupBy()} date, ct.category_id, SUM(ABS(ct.amount)) amount
            FROM cash_transaction ct
            JOIN cash_account ca on ca.id = ct.account_id
            WHERE $_where
            GROUP BY ca.currency, {$grouping->getSqlGroupBy()}, ct.category_id
        ", [
            'company_id' => $company_id,
            'scenario_id' => $scenario_id,
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        ])->fetchAll('date', 2);

        $charData = [
            'categories' => $categories,
            'currencies' => array_combine(
                $currencies,
                array_map(
                    static function (string $currency) {
                        return cashCurrencyVO::fromWaCurrency($currency);
                    },
                    $currencies
                )
            ),
            'data' => [],
        ];

        $categoryIds = array_keys($categories);
        $categoriesCount = count($categoryIds);
        $currentDate = DateTime::createFromFormat($grouping->getPhpDateFormat(),$grouping->getFormattedDate( $dateFrom));

        while ($grouping->getFormattedDate($currentDate) <= $grouping->getFormattedDate($dateTo)) {
            $currentDateStr = $grouping->getFormattedDate($currentDate);

            foreach ($charData['currencies'] as $currency => $item) {
                $charData['data'][(string) $currency][$currentDateStr] = array_combine(
                    $categoryIds,
                    array_fill(0, $categoriesCount, 0.0)
                );
                $charData['data'][(string) $currency][$currentDateStr]['date'] = $currentDateStr;
            }

            if (isset($data[$currentDateStr])) {
                foreach ($data[$currentDateStr] as $d) {
                    $charData['data'][$d['currency']][$currentDateStr][(int) $d['category_id']] = (float) $d['amount'];
                }
            }

            $currentDate = $grouping->getNextDate($currentDate);
        }

        foreach ($charData['data'] as $currency => $datum) {
            $charData['data'][(string) $currency] = array_values($datum);
        }

        return $charData;
    }
}
