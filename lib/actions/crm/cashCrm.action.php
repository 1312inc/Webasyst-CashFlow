<?php

/**
 * Class cashCrmUserAction
 */
class cashCrmAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaNotFoundException
     * @throws kmwaForbiddenException
     */
    public function runAction($params = null)
    {
        if (!cash()->getUser()->hasAccessToApp()) {
            throw new kmwaForbiddenException();
        }

        $contractor = new waContact(waRequest::get('contact', 0, waRequest::TYPE_INT));
        if (!$contractor->exists()) {
            throw new kmwaNotFoundException('Contact not found');
        }

        $filterDto = new cashTransactionPageFilterDto(cashTransactionPageFilterDto::FILTER_ACCOUNT, '');
        $startDate = DateTime::createFromFormat('Y-m-d|', date('Y-m-d', strtotime('-1312 years')));
        $today = DateTime::createFromFormat('Y-m-d|', date('Y-m-d'));
        $selectedChartPeriod = new cashGraphPeriodVO(cashGraphPeriodVO::YEARS_PERIOD, 1312);

        $repository = cash()->getEntityRepository(cashTransaction::class);
        $calcService = new cashCalculationService();

        $contactTransactions = $repository->findForContractor($startDate, $today, $contractor, $filterDto);
        $completedOnDate = $calcService->getOnHandDetailedCategoriesForContractor(
            $startDate,
            $today,
            $contractor,
            $filterDto->contact
        );
        $pagination = new cashPagination('#/');
        $pagination->setTotalRows(count($contactTransactions));

        $this->view->assign(
            [
                'contactTransactions' => $contactTransactions,
                'selectedChartPeriod' => $selectedChartPeriod,
                'completedOnDate' => $completedOnDate,
                'pagination' => $pagination,
                'filter' => $filterDto,
            ]
        );
    }
}
