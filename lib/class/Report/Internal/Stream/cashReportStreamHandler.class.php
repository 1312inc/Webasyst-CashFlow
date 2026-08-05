<?php

final class cashReportStreamHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'stream';
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

        $current_company_id = $params['company'] ?? 0;
        $companies = [['id' => 0, 'name' => _w('Все компании')]] + cash()->getModel('cashCompany')->getAll('id');
        $reportService = new cashReportStreamService();
        $data = $reportService->getDataForPeriod($current_company_id, $dateFrom, $dateTo);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportStream.html'),
            [
                'companies' => $companies,
                'current_company_id' => $current_company_id,
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'data' => $data,
            ],
            true
        );
    }
}
