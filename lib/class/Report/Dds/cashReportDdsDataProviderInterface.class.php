<?php

/**
 * Interface cashReportDdsDataProviderInterface
 */
interface cashReportDdsDataProviderInterface
{
    /**
     * @param cashReportDdsPeriod $period
     *
     * @return array
     */
    public function getDataForPeriod(cashReportDdsPeriod $period): array;
}
