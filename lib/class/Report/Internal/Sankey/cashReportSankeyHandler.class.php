<?php

final class cashReportSankeyHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'sankey';
    }

    public function handle(array $params): string
    {
        $reportService = new cashReportSankeyService();

        $year = $params['year'] ?? 0;
        if (empty($year)) {
            $year = date('Y');
        }
        $currentPeriod = cashReportPeriod::createForYear($year);

        $periods = (new cashReportPeriodsFactory())->getPeriodsByYear();

        $data = $reportService->getDataForYear($currentPeriod->getStart());

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportSankey.html'),
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'grouping' => $currentPeriod->getGrouping(),
            ],
            true
        );
    }
}
