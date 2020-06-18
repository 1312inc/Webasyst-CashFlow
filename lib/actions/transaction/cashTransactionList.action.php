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

        $settings = new cashTransactionListSettingsDto($this->filterDto);

        /**
         * UI in transactions page export dropdown menu
         *
         * @event backend_transactions_export
         *
         * @param cashExportEvent $event Event object with cashTransactionPageFilterDto as object
         *
         * @return string HTML output
         */
        $event = new cashExportEvent($this->filterDto, $this->startDate, $this->endDate);
        $eventResult = cash()->waDispatchEvent($event);

        $justSavedTransactions = wa()->getStorage()->get('cash_just_saved_transactions') ?: [];
        wa()->getStorage()->remove('cash_just_saved_transactions');

        $this->view->assign(
            [
                'upcoming' => $upcoming,
                'completed' => $completed,
                'filter' => $this->filterDto,
                'selectedChartPeriod' => $this->periodChart,
                'selectedForecastPeriod' => $this->periodForecast,
                'upcomingOnDate' => $upcomingOnDate,
                'completedOnDate' => $completedOnDate,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'listSettings' => $settings,

                'backend_transactions_export' => $eventResult,

                'justSavedTransactions' => $justSavedTransactions,
            ]
        );
    }
}
