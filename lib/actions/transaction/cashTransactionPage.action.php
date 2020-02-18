<?php

/**
 * Class cashTransactionPageAction
 */
class cashTransactionPageAction extends cashViewAction
{
    /**
     * @var DateTime
     */
    protected $startDate;

    /**
     * @var DateTime
     */
    protected $endDate;

    /**
     * @var cashGraphPeriodVO
     */
    protected $periodChart;

    /**
     * @var cashGraphPeriodVO
     */
    protected $periodForecast;

    /**
     * @var cashGraphService
     */
    protected $graphService;

    /**
     * @var cashTransactionPageFilterDto
     */
    protected $filterDto;

    /**
     * @var DateTime
     */
    protected $today;

    /**
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     * @throws kmwaForbiddenException
     */
    public function preExecute()
    {
        parent::preExecute();

        $id = waRequest::get('id', 0, waRequest::TYPE_STRING_TRIM);
        $filterType = waRequest::get('filter', '', waRequest::TYPE_STRING_TRIM);

        $this->graphService = new cashGraphService();
        $this->filterDto = new cashTransactionPageFilterDto($filterType, $id);

        $startDate = waRequest::get('start_date', [], waRequest::TYPE_STRING_TRIM);
        $endDate = waRequest::get('end_date', [], waRequest::TYPE_STRING_TRIM);
        if (!$startDate) {
            $this->periodChart = $this->graphService->getDefaultChartPeriod();
            $this->startDate = $this->periodChart->getDate();
        } else {
            $this->startDate = new DateTime($startDate);
            $this->periodChart = $this->graphService->getChartPeriodByDate($this->startDate);
        }
        if (!$endDate) {
            $this->periodForecast = $this->graphService->getDefaultForecastPeriod();
            $this->endDate = $this->periodForecast->getDate();
        } else {
            $this->endDate = new DateTime($endDate);
            $this->periodForecast = $this->graphService->getForecastPeriodByDate($this->endDate);
        }

        $this->graphService->saveChartPeriodVo($this->periodChart);
        $this->graphService->saveForecastPeriodVo($this->periodForecast);

        $this->today = new DateTime();

        $this->repeatNeverEndingTransactions();
    }

    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaLogicException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $calcService = new cashCalculationService();

        // cash on hand today
        $onHandsToday = [];
        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
            $onHandsToday = $calcService->getOnHandOnDate($this->today, $this->filterDto->entity);
        }

        // cash on hand end day
        $onHandsEndday = $calcService->getOnHandOnDate($this->endDate, $this->filterDto->entity);

        // total on hand
//        $farFarFuture = (new DateTime())->modify('+100 years');
//        $onHandsTotal = $calcService->getOnHandOnDate($farFarFuture, new cashAccount());

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

        $this->view->assign(
            [
                'filter' => $this->filterDto,
                'onHandsToday' => $onHandsToday,
                'onHandsEndday' => $onHandsEndday,
//                'onHandsTotal' => $onHandsTotal,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'currentDate' => $this->today->format('Y-m-d'),
                'selectedChartPeriod' => $this->periodChart,
                'selectedForecastPeriod' => $this->periodForecast,
                'backend_transactions_export' => $eventResult,
            ]
        );
    }

    /**
     * @throws waException
     */
    private function repeatNeverEndingTransactions()
    {
        $trans = cash()->getEntityRepository(cashRepeatingTransaction::class)->findNeverEndingAfterDate($this->endDate);
        /** @var cashTransactionRepository $transRep */
        $transRep = cash()->getEntityRepository(cashTransaction::class);
        $repeater = new cashTransactionRepeater();
        foreach ($trans as $transaction) {
            try {
                cash()->getLogger()->debug(
                    sprintf(
                        'Trying to extend repeating transaction #%d starting from %s',
                        $transaction->getId(),
                        $transaction->getDataField('last_transaction_date')
                    )
                );
                $lastT = $transRep->findLastByRepeatingId($transaction->getId());
                $date = $lastT instanceof cashTransaction ? $lastT->getDate() : $transaction->getDataField('last_transaction_date');
                $repeater->repeat($transaction, new DateTime($date));
            } catch (Exception $ex) {
                cash()->getLogger()->error(
                    sprintf('Can`t extend repeating transaction #%d', $transaction->getId()),
                    $ex
                );
            }
        }
    }
}
