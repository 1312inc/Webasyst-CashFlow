<?php

final class cashReportSankeyHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'sankey';
    }

    public function handle(array $params): string
    {
        $dateFrom = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $params['from'] ?? date('Y-m-d', strtotime('-365 days'))
        );
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $params['to'] ?? date('Y-m-d'));
        if ($dateTo === false || $dateFrom === false) {
            throw new cashValidateException('Invalid time interval');
        }

        $company_id = $params['company_id'] ?? null;
        $scenario_id = $params['scenario_id'] ?? null;
        if ($company_id) {
            $company = cash()->getModel('cashCompany')->getById($company_id);
            $company_name = ifset($company, 'name', _w('No name'));
            $scenario_name = _w('Basic scenario');
        }
        if ($scenario_id) {
            $scenario = cash()->getModel('cashScenario')->getById($scenario_id);
            $scenario_name = ifset($scenario, 'name', _w('No name'));
        }

        $reportService = new cashReportSankeyService();
        $data = $reportService->getDataForPeriod($company_id, $scenario_id, $dateFrom, $dateTo);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportSankey.html'),
            [
                'company_name' => ifempty($company_name),
                'scenario_name' => ifempty($scenario_name),
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ],
            true
        );
    }
}
