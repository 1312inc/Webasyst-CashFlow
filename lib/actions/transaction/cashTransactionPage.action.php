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
     * @throws Exception
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

        if ($this->filterDto->type !== cashTransactionPageFilterDto::FILTER_IMPORT) {
            $this->graphService->saveChartPeriodVo($this->periodChart);
            $this->graphService->saveForecastPeriodVo($this->periodForecast);
        }

        $this->today = new DateTime();

        cash()->getEventDispatcher()->dispatch(new cashEvent(cashEventStorage::TRANSACTION_PAGE_PREEXECUTE, $this->endDate));
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

        $settings = new cashTransactionListSettingsDto($this->filterDto);

        // cash on hand end day
        $onHandsEndday = $calcService->getOnHandOnDate($this->endDate, $this->filterDto->entity);

        // total on hand
//        $farFarFuture = (new DateTime())->modify('+100 years');
//        $onHandsTotal = $calcService->getOnHandOnDate($farFarFuture, new cashAccount());

        $this->view->assign(
            [
                'filter' => $this->filterDto,
                'onHandsToday' => $onHandsToday,
//                'onHandsTotal' => $onHandsTotal,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'currentDate' => $this->today->format('Y-m-d'),
                'selectedChartPeriod' => $this->periodChart,
                'selectedForecastPeriod' => $this->periodForecast,
                'listSettings' => $settings,
                'onHandsEndday' => $onHandsEndday,
            ]
        );
    }
}
