<?php

interface cashReportDdsDataProviderInterface
{
    /**
     * @param int|null $company_id
     * @param int|null $scenario_id
     * @param cashReportPeriod $period
     * @return cashReportDdsStatDto[]
     */
    public function getDataForPeriod(?int $company_id, ?int $scenario_id, cashReportPeriod $period): array;
}
