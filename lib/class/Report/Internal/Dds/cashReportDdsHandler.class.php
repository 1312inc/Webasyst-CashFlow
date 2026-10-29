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
        $company_id = $params['company_id'] ?? null;
        $scenario_id = $params['scenario_id'] ?? null;
        if (empty($year)) {
            $year = date('Y');
        }
        if ($company_id) {
            $company = cash()->getModel('cashCompany')->getById($company_id);
            $company_name = ifset($company, 'name', _w('Без названия'));
        }
        if ($scenario_id) {
            $scenario = cash()->getModel('cashScenario')->getById($scenario_id);
            $scenario_name = ifset($scenario, 'name', _w('Без названия'));
        } else {
            $scenario_name = _w('Базовый сценарий');
        }

        $currentPeriod = cashReportPeriod::createForYear($year);
        $data = $reportService->getDataForTypeAndPeriod($company_id, $scenario_id, $type, $currentPeriod);
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
                'company_name' => ifempty($company_name),
                'scenario_name' => $scenario_name,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
                'is_imaginary' => in_array(true, array_unique(array_column($data, 'is_imaginary')), true)
            ],
            true
        );
    }
}
