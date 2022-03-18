<?php

final class cashDdsPluginHandler implements cashReportHandlerInterface
{
    public function canHandle(string $identifier): bool
    {
        return $identifier === 'dds';
    }

    public function handle(array $params): string
    {
        $reportService = new cashDdsPluginService();

        $year = $params['year'] ?? 0;
        if (empty($year)) {
            $year = date('Y');
        }
        $currentPeriod = cashDdsPluginPeriod::createForYear($year);

        $ddsTypes = $reportService->getTypes();
        /** @var cashDdsPluginTypeDto|string $type */
        $type = $params['type'] ?? cashDdsPlugin::TYPE_CATEGORY;
        if (isset($ddsTypes[$type])) {
            $type = $ddsTypes[$type];
        } else {
            throw new waException(sprintf('Unknown report type: %s', $type));
        }

        $periods = $reportService->getPeriodsByYear();

        $data = $reportService->getDataForTypeAndPeriod($type, $currentPeriod);
        $chartData = $reportService->formatDataForPie($data, $type, $currentPeriod);

        $type = array_reduce($data, static function ($type, cashDdsPluginStatDto $dto) {
            if ($dto->entity->isIncome()) {
                $type->incomeEntities++;
            }
            if ($dto->entity->isExpense()) {
                $type->expenseEntities++;
            }

            return $type;
        }, $type);

        return wa()->getView()->renderTemplate(
            wa()->getAppPath('plugins/dds/templates/Report.html'),
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'ddsTypes' => $ddsTypes,
                'type' => $type,
                'data' => $data,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
            ],
            true
        );
    }
}
