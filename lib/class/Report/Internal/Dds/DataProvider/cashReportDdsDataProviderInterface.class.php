<?php

interface cashReportDdsDataProviderInterface
{
    /**
     * @return cashReportDdsStatDto[]
     */
    public function getDataForPeriod(cashReportDdsPeriod $period): array;
}
