<?php

/**
 * Class cashReportDdsAction
 */
class cashReportDdsAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     */
    public function runAction($params = null)
    {
        $reportService = new cashReportDds();

        $year = waRequest::get('year', 0, waRequest::TYPE_INT);
        if (empty($year)) {
            $year = date('Y');
        }
        $currentPeriod = cashReportDdsPeriod::createForYear($year);

        $ddsTypes = $reportService->getTypes();
        /** @var cashReportDdsTypeDto|string $type */
        $type = waRequest::get('type', cashReportDds::TYPE_CATEGORY, waRequest::TYPE_STRING_TRIM);
        if (isset($ddsTypes[$type])) {
            $type = $ddsTypes[$type];
        } else {
            throw new waException(sprintf('Unknown report type: %s', $type));
        }

        $periods = $reportService->getPeriodsByYear();

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

        $this->view->assign(
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'ddsTypes' => $ddsTypes,
                'type' => $type,
                'data' => $data,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_UNICODE),
            ]
        );
    }
}
