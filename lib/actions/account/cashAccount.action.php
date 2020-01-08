<?php

/**
 * Class cashAccountAction
 */
class cashAccountAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws kmwaAssertException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('id', 0, waRequest::TYPE_INT);
        $periodChart = waRequest::get('period_chart', [], waRequest::TYPE_ARRAY);
        $periodForecast = waRequest::get('period_forecast', [], waRequest::TYPE_ARRAY);

        if (!$periodChart) {
            $periodChart = cashGraphService::getDefaultChartPeriod();
        } else {
            $periodChart = new cashGraphPeriodVO($periodChart['period'], $periodChart['value']);
        }

        if (!$periodForecast) {
            $periodForecast = cashGraphService::getDefaultForecastPeriod();
        } else {
            $periodForecast = new cashGraphPeriodVO($periodForecast['period'], $periodForecast['value']);
        }

        $startDate = $periodChart->getDate();
        $endDate = $periodForecast->getDate();

        if ($id) {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
            kmwaAssert::instance($account, cashAccount::class);
        } else {
            $account = cash()->getEntityFactory(cashAccount::class)->createAllAccount();
        }

        $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);

        $calcService = new cashCalculationService();

        // cash on hand today
        $onHandsToday = $calcService->getOnHandOnDate(new DateTime(), $account);

        // cash on hand end day
        $onHandsEndday = $calcService->getOnHandOnDate($endDate, $account);

        // total income
        // total expense

        $this->view->assign(
            [
                'account' => $accountDto,
                'onHandsToday' => $onHandsToday,
                'onHandsEndday' => $onHandsEndday,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'selectedChartPeriod' => $periodChart,
                'selectedForecastPeriod' => $periodForecast,
            ]
        );
    }
}
