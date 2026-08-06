<?php

final class cashReportDdsHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'dds';
    }

    public function handle(array $params): string
    {
        $reportService = new cashReportDdsService();
        $ddsTypes = $reportService->getTypes();
        /** @var cashReportDdsTypeDto|string $type */
        $type = $params['type'] ?? cashReportDdsService::TYPE_CATEGORY;
        if (isset($ddsTypes[$type])) {
            $type = $ddsTypes[$type];
        } else {
            throw new waException(sprintf('Unknown report type: %s', $type));
        }

        $year = $params['year'] ?? 0;
        if (empty($year)) {
            $year = date('Y');
        }
        $current_company_id = $params['company'] ?? 0;
        $companies = [['id' => 0, 'name' => _w('All companies')]] + cash()->getModel('cashCompany')->getAll('id');
        $currentPeriod = cashReportPeriod::createForYear($year);
        $data = $reportService->getDataForTypeAndPeriod($current_company_id, $type, $currentPeriod);
        $chartData = $reportService->formatDataForPie($data, $type, $currentPeriod);

        $type = array_reduce($data, static function ($type, cashReportDdsStatDto $dto) {
            if ($dto->entity->isIncome()) {
                $type->incomeEntities++;
            }
            if ($dto->entity->isExpense()) {
                $type->expenseEntities++;
            }

            return $type;
        }, $type);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportDds.html'),
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => (new cashReportPeriodsFactory())->getPeriodsByYear(),
                'ddsTypes' => $ddsTypes,
                'type' => $type,
                'data' => $data,
                'companies' => $companies,
                'current_company_id' => $current_company_id,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
                'is_imaginary' => in_array(true, array_unique(array_column($data, 'is_imaginary')), true)
            ],
            true
        );
    }
}
