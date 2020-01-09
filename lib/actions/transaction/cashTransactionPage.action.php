<?php

/**
 * Class cashTransactionPageAction
 */
class cashTransactionPageAction extends cashViewAction
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
        $startDate = waRequest::get('start_date', [], waRequest::TYPE_STRING_TRIM);
        $endDate = waRequest::get('end_date', [], waRequest::TYPE_STRING_TRIM);

        $graphService = new cashGraphService();

        if (!$startDate) {
            $periodChart = $graphService->getDefaultChartPeriod();
            $startDate = $periodChart->getDate();
        } else {
            $startDate = new DateTime($startDate);
            $periodChart  =$graphService->getChartPeriodByDate($startDate);
        }

        if (!$endDate) {
            $periodForecast = $graphService->getDefaultForecastPeriod();
            $endDate = $periodForecast->getDate();
        } else {
            $endDate = new DateTime($endDate);
            $periodForecast  =$graphService->getForecastPeriodByDate($endDate);
        }

        $today = new DateTime();

        if ($id) {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($id);
            kmwaAssert::instance($account, cashAccount::class);
        } else {
            $account = cash()->getEntityFactory(cashAccount::class)->createAllAccount();
        }

        $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);

        $calcService = new cashCalculationService();

        // cash on hand today
        $onHandsToday = $calcService->getOnHandOnDate($today, $account);

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
                'currentDate' => $today->format('Y-m-d'),
                'selectedChartPeriod' => $periodChart,
                'selectedForecastPeriod' => $periodForecast,
            ]
        );
    }
}
