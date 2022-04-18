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

        $dateFrom = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $params['from'] ?? date('Y-m-d', strtotime('-365 days'))
        );
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $params['to'] ?? date('Y-m-d'));
        if ($dateTo === false || $dateFrom === false) {
            throw new cashValidateException('Wrong dates');
        }

        if (abs($dateTo->diff($dateFrom)->days) > 365) {
            throw new cashValidateException('Wrong period');
        }

        $data = $reportService->getDataForPeriod($dateFrom, $dateTo);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportSankey.html'),
            [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ],
            true
        );
    }
}
