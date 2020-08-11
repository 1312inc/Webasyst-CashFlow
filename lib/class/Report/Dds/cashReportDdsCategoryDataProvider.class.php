<?php

/**
 * Class cashReportDdsCategoryDataProvider
 */
class cashReportDdsCategoryDataProvider implements cashReportDdsDataProviderInterface
{
    private $transactionModel;

    public function __construct()
    {
        $this->transactionModel = cash()->getModel(cashTransaction::class);
    }

    /**
     * @param cashReportDdsPeriod $period
     *
     * @return array
     */
    public function getDataForPeriod(cashReportDdsPeriod $period): array
    {
        $sql = <<<SQL
select * from cash_transaction group by MONTH(date);
SQL;

    }
}
