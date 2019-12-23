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
                'backend_sidebar' => $eventResult,
            ]
        );
    }
}
