<?php

/**
 * Interface cashReportDdsDataProviderInterface
 */
interface cashReportDdsDataProviderInterface
{
    /**
     * @param cashReportDdsPeriod $period
     *
     * @return cashReportDdsStatDto[]
     */
    public function getDataForPeriod(cashReportDdsPeriod $period): array;
}
