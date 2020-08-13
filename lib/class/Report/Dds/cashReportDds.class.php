<?php

/**
 * Class cashReportDds
 */
class cashReportDds
{
    const TYPE_CATEGORY   = 'category';
    const TYPE_CONTRACTOR = 'contractor';
    const TYPE_ACCOUNT    = 'account';

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
            $periods[] = cashReportDdsPeriod::createForYear($yearsWithTransaction);
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
        }

        return $data;
    }

    /**
     * @return cashReportDdsTypeDto[]
     */
    public function getTypes(): array
    {
        return [
            self::TYPE_CATEGORY => new cashReportDdsTypeDto(self::TYPE_CATEGORY, _w('Categories')),
            self::TYPE_ACCOUNT => new cashReportDdsTypeDto(self::TYPE_ACCOUNT, _w('Accounts')),
            self::TYPE_CONTRACTOR => new cashReportDdsTypeDto(self::TYPE_CONTRACTOR, _w('Contractors')),
        ];
    }
}
