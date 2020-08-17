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

        $year = waRequest::get('year', date('Y'), waRequest::TYPE_INT);
        $currentPeriod = cashReportDdsPeriod::createForYear($year);

        $ddsTypes = $reportService->getTypes();
        $type = waRequest::get('type', cashReportDds::TYPE_CATEGORY, waRequest::TYPE_STRING_TRIM);
        if (isset($ddsTypes[$type])) {
            $type = $ddsTypes[$type];
        }

        $periods = $reportService->getPeriodsByYear();

        $data = $reportService->getDataForTypeAndPeriod($type, $currentPeriod);
        $chartData = $reportService->formatDataForPie($data, $type, $currentPeriod);

        $entityStat = [
            'incomes' => 0,
            'expenses' => 0,
        ];
        $entityStat = array_reduce($data, static function ($entityStat, cashReportDdsStatDto $dto) {
            if ($dto->entity->isIncome()) {
                $entityStat['incomes']++;
            }
            if ($dto->entity->isExpense()) {
                $entityStat['expenses']++;
            }

            return $entityStat;
        }, $entityStat);

        $this->view->assign(
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'ddsTypes' => $ddsTypes,
                'type' => $type,
                'data' => $data,
                'grouping' => $currentPeriod->getGrouping(),
                'chartData' => json_encode($chartData, JSON_UNESCAPED_SLASHES),
                'entityStat' => $entityStat,
            ]
        );
    }
}
