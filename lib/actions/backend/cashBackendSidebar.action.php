<?php

/**
 * Class cashBackendSidebarAction
 */
class cashBackendSidebarAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws waException
     */
    public function runAction($params = null)
    {
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActive();
        $accountDtos = cashAccountDto::createFromEntities($accounts);

        $incomes = cash()->getEntityRepository(cashCategory::class)->findAllByType(cashCategory::TYPE_INCOME);
        $incomeDtos = cashCategoryDto::createFromEntities($incomes);

        $expenses = cash()->getEntityRepository(cashCategory::class)->findAllByType(cashCategory::TYPE_EXPENSE);
        $expenseDtos = cashCategoryDto::createFromEntities($expenses);

        /**
         * UI in main sidebar
         *
         * @event backend_sidebar
         *
         * @param kmwaEventInterface $event Event object
         *
         * @return string HTML output
         */
        $event = new cashEvent(cashEventStorage::WA_BACKEND_SIDEBAR);
        $eventResult = cash()->waDispatchEvent($event);

        $this->view->assign(
            [
                'accounts' => $accountDtos,
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'backend_sidebar' => $eventResult,
            ]
        );
    }
}
