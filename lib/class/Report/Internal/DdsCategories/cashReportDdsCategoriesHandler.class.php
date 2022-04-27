<?php

final class cashReportDdsCategoriesHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'categories';
    }

    public function handle(array $params): string
    {
        $reportService = new cashReportDdsService();

        $year = $params['year'] ?? 0;
        if (empty($year)) {
            $year = date('Y');
        }
        $currentPeriod = cashReportPeriod::createForYear($year);

        $type = new cashReportDdsTypeDto(cashReportDdsService::TYPE_CATEGORY, _w('Categories'), true);

        $periods = (new cashReportPeriodsFactory())->getPeriodsByYear();

        $data = $reportService->getDataForTypeAndPeriod($type, $currentPeriod);
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
                'categories' => $categories,
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'type' => $type,
                'data' => $data,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
            ],
            true
        );
    }
}
