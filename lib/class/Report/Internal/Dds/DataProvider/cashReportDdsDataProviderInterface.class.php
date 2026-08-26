<?php

interface cashReportDdsDataProviderInterface
{
    /**
     * @param int $company_id
     * @param cashReportPeriod $period
     * @return cashReportDdsStatDto[]
     */
    public function getDataForPeriod(int $company_id, cashReportPeriod $period): array;
}
