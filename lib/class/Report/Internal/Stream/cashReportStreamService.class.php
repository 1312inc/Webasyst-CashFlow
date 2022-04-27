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

    public function getDataForPeriod(DateTimeImmutable $dateFrom, DateTimeImmutable $dateTo): array
    {
        $grouping = new cashReportSankeyDataGrouping($dateFrom, $dateTo);

        $sqlCurrencies = <<<SQL
SELECT ca.currency
FROM cash_transaction ct
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
  AND ct.category_id != s:transfer_id
GROUP BY ca.currency
SQL;
        $currencies = $this->model->query($sqlCurrencies, [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        ])->fetchAll('currency');
        $currencies = array_column($currencies, 'currency');

        $sqlCategories = <<<SQL
SELECT cc.*
FROM cash_transaction ct
         JOIN cash_account ca on ca.id = ct.account_id
         JOIN cash_category cc on cc.id = ct.category_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
  AND ct.category_id != s:transfer_id
GROUP BY ct.category_id
SQL;
        $categories = $this->model->query($sqlCategories, [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        ])->fetchAll('id');

        $sql = <<<SQL
SELECT ca.currency, {$grouping->getSqlGroupBy()} date, ct.category_id, SUM(ABS(ct.amount)) amount
FROM cash_transaction ct
         JOIN cash_account ca on ca.id = ct.account_id
WHERE ct.is_archived = 0
  AND ca.is_archived = 0
  AND ct.date >= s:date_from
  AND ct.date <= s:date_to
  AND ct.category_id != s:transfer_id
GROUP BY ca.currency, {$grouping->getSqlGroupBy()}, ct.category_id
SQL;

        $data = $this->model->query(
            $sql,
            [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'transfer_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
            ]
        )->fetchAll('date', 2);

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
                $charData['data'][$currency][$currentDateStr] = array_combine(
                    $categoryIds,
                    array_fill(0, $categoriesCount, 0.0)
                );
                $charData['data'][$currency][$currentDateStr]['date'] = $currentDateStr;
            }

            if (isset($data[$currentDateStr])) {
                foreach ($data[$currentDateStr] as $d) {
                    $charData['data'][$d['currency']][$currentDateStr][(int) $d['category_id']] = (float) $d['amount'];
                }
            }

            $currentDate = $grouping->getNextDate($currentDate);
        }

        foreach ($charData['data'] as $currency => $datum) {
            $charData['data'][$currency] = array_values($datum);
        }

        return $charData;
    }
}
