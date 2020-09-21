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
     * @var cashPagination
     */
    protected $pagination;

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

        switch ($this->filterDto->type) {
            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                if ($this->filterDto->id
                    && !cash()->getContactRights()->hasMinimumAccessToAccount($this->filterDto->contact, $this->filterDto->id)
                ) {
                    throw new kmwaForbiddenException(_w('You are not allowed to access this account'));
                }
                break;

            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                if (!cash()->getContactRights()->hasMinimumAccessToCategory($this->filterDto->contact, $this->filterDto->id)) {
                    throw new kmwaForbiddenException(_w('You are not allowed to access this category'));
                }
                break;

            case cashTransactionPageFilterDto::FILTER_IMPORT:
                if (!cash()->getContactRights()->canImport($this->filterDto->contact)) {
                    throw new kmwaForbiddenException(_w('You are not allowed to access this import'));
                }
                break;
        }

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
        $this->pagination = new cashPagination(
            sprintf(
                '#/%s/%d/%s/%s',
                $this->filterDto->type,
                $this->filterDto->id,
                $this->startDate->format('Y-m-d'),
                $this->endDate->format('Y-m-d')
            )
        );
        $this->pagination
            ->setStart(waRequest::get('start', 0, waRequest::TYPE_INT) ?: 0)
            ->setLimit(waRequest::get('limit', 0, waRequest::TYPE_INT) ?: cashPagination::LIMIT);

        cash()->getEventDispatcher()->dispatch(
            new cashEvent(cashEventStorage::TRANSACTION_PAGE_PREEXECUTE, $this->endDate)
        );
    }

    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaLogicException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $calcService = new cashCalculationService();

        // cash on hand today
        $onHandsToday = [];
        $onHand = null;
        if ($this->filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
            $onHandsToday = $calcService->getOnHandOnDate(
                $this->today,
                $this->filterDto->contact,
                $this->filterDto->entity
            );

            if (!$onHandsToday && $this->filterDto->id) {
                $onHand = new cashStatOnHandDto(
                    cashCurrencyVO::fromWaCurrency($this->filterDto->entity->getCurrency()),
                    new cashStatOnDateDto(0,0,0)
                );
            }
        }

        $settings = new cashTransactionListSettingsDto($this->filterDto, $this->periodForecast);

        // cash on hand end day
        $onHandsEndday = $calcService->getOnHandOnDate(
            $this->endDate,
            $this->filterDto->contact,
            $this->filterDto->entity
        );

        // total on hand
//        $farFarFuture = (new DateTime())->modify('+100 years');
//        $onHandsTotal = $calcService->getOnHandOnDate($farFarFuture, new cashAccount());

        $this->view->assign(
            [
                'filter' => $this->filterDto,
                'onHandsToday' => $onHandsToday,
                'onHand' => $onHand,
//                'onHandsTotal' => $onHandsTotal,
                'startDate' => $this->startDate->format('Y-m-d'),
                'endDate' => $this->endDate->format('Y-m-d'),
                'currentDate' => $this->today->format('Y-m-d'),
                'selectedChartPeriod' => $this->periodChart,
                'selectedForecastPeriod' => $this->periodForecast,
                'listSettings' => $settings,
                'onHandsEndday' => $onHandsEndday,
                'pagination' => $this->pagination,
                'fromShopScriptImport' => false,
                'hasAccessToEdit' => $this->filterDto->type ===cashTransactionPageFilterDto::FILTER_ACCOUNT
                    ? cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $this->filterDto->id)
                    : cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $this->filterDto->id),
            ]
        );

        $fromSsimport = wa()->getStorage()->get(cashShopIntegration::SESSION_SSIMPORT);
        if ($fromSsimport) {
            wa()->getStorage()->del(cashShopIntegration::SESSION_SSIMPORT);
            $this->view->assign(['fromShopScriptImport' => $fromSsimport]);
        }
    }
}
