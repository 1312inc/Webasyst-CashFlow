<?php

/**
 * Class cashTransactionListAction
 */
class cashTransactionListAction extends cashTransactionPageAction
{
    /**
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function runAction($params = null)
    {
        /** @var cashTransactionRepository $repository */
        $repository = cash()->getEntityRepository(cashTransaction::class);
        $tomorrow = new DateTime('tomorrow');
        $calcService = new cashCalculationService();

        $upcoming = array_reverse(
            $repository->findByDates($tomorrow, $this->endDate, $this->filterDto),
            true
        );
        $completed = array_reverse(
            $repository->findByDates($this->startDate, $this->today, $this->filterDto),
            true
        );

        $upcomingOnDate = $calcService->getOnHandDetailedCategories(
            $tomorrow,
            $this->endDate,
            $this->filterDto->entity,
            $this->filterDto->type
        );
        $completedOnDate = $calcService->getOnHandDetailedCategories(
            $this->startDate,
            $this->today,
            $this->filterDto->entity,
            $this->filterDto->type
        );

        // cash on hand end day
        $onHandsEndday = $calcService->getOnHandOnDate($this->endDate, $this->filterDto->entity);

        $settings = new cashTransactionListSettingsDto($this->filterDto);

        $this->view->assign(
            [
                'upcoming' => $upcoming,
                'completed' => $completed,
                'filter' => $this->filterDto,
                'selectedChartPeriod' => $this->periodChart,
                'selectedForecastPeriod' => $this->periodForecast,
                'upcomingOnDate' => $upcomingOnDate,
                'completedOnDate' => $completedOnDate,
                'onHandsEndday' => $onHandsEndday,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'listSettings' => $settings,
            ]
        );
    }
}
