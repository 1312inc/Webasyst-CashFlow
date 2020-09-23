<?php

/**
 * Class cashReportDds
 */
class cashReportDds
{
    const TYPE_CATEGORY   = 'category';
    const TYPE_CONTRACTOR = 'contractor';
    const TYPE_ACCOUNT    = 'account';

    const ALL_INCOME_KEY  = 'all_income';
    const ALL_EXPENSE_KEY = 'all_expense';

    /**
     * @return cashReportDdsPeriod[]
     * @throws waException
     */
    public function getPeriodsByYear(): array
    {
        $periods = [];

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        foreach ($model->getYearsWithTransactions() as $yearsWithTransaction) {
            if ($yearsWithTransaction > 1900) {
                $periods[] = cashReportDdsPeriod::createForYear($yearsWithTransaction);
            }
        }

        return $periods;
    }

    /**
     * @param cashReportDdsTypeDto $type
     * @param cashReportDdsPeriod  $period
     *
     * @return array
     * @throws waException
     */
    public function getDataForTypeAndPeriod(cashReportDdsTypeDto $type, cashReportDdsPeriod $period): array
    {
        switch ($type->id) {
            case self::TYPE_CATEGORY:
                $data = (new cashReportDdsCategoryDataProvider())->getDataForPeriod($period);

                break;

            case self::TYPE_ACCOUNT:
                $data = (new cashReportDdsAccountDataProvider())->getDataForPeriod($period);

                break;

            case self::TYPE_CONTRACTOR:
                $data = (new cashReportDdsContractorDataProvider())->getDataForPeriod($period);

                break;
        }

        return $data;
    }

    /**
     * @param array                $data
     * @param cashReportDdsTypeDto $type
     * @param cashReportDdsPeriod  $period
     *
     * @return array
     */
    public function formatDataForPie(array $data, cashReportDdsTypeDto $type, cashReportDdsPeriod $period): array
    {
        $chartData = [
            self::ALL_INCOME_KEY => [],
            self::ALL_EXPENSE_KEY => [],
        ];

        /** @var cashReportDdsStatDto $datum */
        foreach ($data as $datum) {
            $id = $datum->entity->getId();
            if (in_array($id, [self::ALL_INCOME_KEY, self::ALL_EXPENSE_KEY])) {
                continue;
            }

            $incOrExp = $datum->entity->isExpense() ? self::ALL_EXPENSE_KEY : self::ALL_INCOME_KEY;
            /** @var cashCurrencyVO $currency */
            foreach ($datum->currencies as $currency) {
                $currencyCode = $currency->getCode();
                if (!isset($chartData[$incOrExp][$currencyCode])) {
                    $chartData[$incOrExp][$currencyCode] = new cashReportDdsPieDto($currency);
                }
                $chartData[$incOrExp][$currencyCode]->columns[$id] = [$datum->entity->getName()];
                if ($datum->entity->getColor()) {
                    $chartData[$incOrExp][$currencyCode]->colors[$datum->entity->getName()] = [
                        $datum->entity->getColor()
                    ];
                }
            }

            foreach ($datum->valuesPerPeriods as $valuesPerPeriod) {
                foreach ($valuesPerPeriod as $currency => $value) {
                    $chartData[$incOrExp][$currency]->columns[$id][] = (float) abs($value['per_month']);
                }
            }
        }

        return $chartData;
    }

    /**
     * @return cashReportDdsTypeDto[]
     */
    public function getTypes(): array
    {
        return [
            self::TYPE_CATEGORY => new cashReportDdsTypeDto(self::TYPE_CATEGORY, _w('Categories'), true),
            self::TYPE_ACCOUNT => new cashReportDdsTypeDto(self::TYPE_ACCOUNT, _w('Accounts'), true),
//            self::TYPE_CONTRACTOR => new cashReportDdsTypeDto(self::TYPE_CONTRACTOR, _w('Contractors'), true),
        ];
    }
}
