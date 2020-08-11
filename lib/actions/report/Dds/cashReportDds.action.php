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
        $type = waRequest::get('type', null, waRequest::TYPE_STRING_TRIM);
        if (isset($ddsTypes[$type])) {
            $type = $ddsTypes[$type];
        }

        $periods = $reportService->getPeriodsByYear();

        $this->view->assign(
            [
                'currentPeriod' => $currentPeriod,
                'reportPeriods' => $periods,
                'ddsTypes' => $ddsTypes,
                'type' => $type,
            ]
        );
    }
}
