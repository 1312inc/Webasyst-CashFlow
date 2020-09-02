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

        $contact = new waContact(waRequest::get('contact', 0, waRequest::TYPE_INT));
        if (!$contact->exists()) {
            throw new kmwaNotFoundException('Contact not found');
        }

        $filterDto = new cashTransactionPageFilterDto(cashTransactionPageFilterDto::FILTER_ACCOUNT, '');
        $startDate = DateTime::createFromFormat('Y-m-d|', date('Y-m-d', strtotime('-1 month')));
        $today = DateTime::createFromFormat('Y-m-d|', date('Y-m-d'));
        $selectedChartPeriod = new cashGraphPeriodVO(cashGraphPeriodVO::DAYS_PERIOD, 30);

        $repository = cash()->getEntityRepository(cashTransaction::class);
        $calcService = new cashCalculationService();

        $contactTransactions = $repository->findForContact($startDate, $today, $contact, $filterDto);
        $completedOnDate = $calcService->getOnHandDetailedCategoriesForContact(
            $startDate,
            $today,
            $contact,
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
