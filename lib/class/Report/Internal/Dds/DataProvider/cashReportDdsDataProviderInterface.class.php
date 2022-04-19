<?php

interface cashReportDdsDataProviderInterface
{
    /**
     * @return cashReportDdsStatDto[]
     */
    public function getDataForPeriod(cashReportPeriod $period): array;
}
