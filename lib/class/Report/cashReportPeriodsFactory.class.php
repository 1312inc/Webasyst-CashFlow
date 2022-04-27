<?php

final class cashReportPeriodsFactory
{
    /**
     * @return cashReportPeriod[]
     * @throws waException
     */
    public function getPeriodsByYear(): array
    {
        $periods = [];

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        foreach ($model->getYearsWithTransactions() as $yearsWithTransaction) {
            if ($yearsWithTransaction > 1900) {
                $periods[] = cashReportPeriod::createForYear($yearsWithTransaction);
            }
        }

        return $periods;
    }
}
