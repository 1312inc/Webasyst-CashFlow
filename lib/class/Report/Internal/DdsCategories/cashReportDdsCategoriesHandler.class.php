<?php

final class cashReportDdsCategoriesHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'categories';
    }

    public function handle(array $params): string
    {
        $year = $params['year'] ?? 0;
        $company_id = $params['company_id'] ?? null;
        $scenario_id = $params['scenario_id'] ?? null;
        if (empty($year)) {
            $year = date('Y');
        }
        if ($company_id) {
            $company = cash()->getModel('cashCompany')->getById($company_id);
            $company_name = ifset($company, 'name', _w('No name'));
            $scenario_name = _w('Basic scenario');
        }
        if ($scenario_id) {
            $scenario = cash()->getModel('cashScenario')->getById($scenario_id);
            $scenario_name = ifset($scenario, 'name', _w('No name'));
        }

        $reportService = new cashReportDdsService();
        $currentPeriod = cashReportPeriod::createForYear($year);
        $type = new cashReportDdsTypeDto(cashReportDdsService::TYPE_CATEGORY, _w('Categories'), true);
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

        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $categories = array_combine(array_column($categories, 'id'), $categories);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('templates/actions/report/internal/ReportDdsCategories.html'),
            [
                'company_name' => ifempty($company_name),
                'scenario_name' => ifempty($scenario_name),
                'categories' => $categories,
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => (new cashReportPeriodsFactory())->getPeriodsByYear(),
                'type' => $type,
                'data' => $data,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
            ],
            true
        );
    }
}
