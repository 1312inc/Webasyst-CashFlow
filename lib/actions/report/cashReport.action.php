<?php

/**
 * Class cashReportAction
 */
class cashReportAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        /**
         * @event backend_reports_menu_item
         *
         * @param cashReportMenuItemEvent $event Event object
         *
         * @return cashReportMenuItemInterface Menu object
         */
        $event = new cashReportMenuItemEvent(cashEventStorage::WA_REPORTS_MENU_ITEM);
        $eventResult = cash()->waDispatchEvent($event);
        foreach ($eventResult as $plugin => $results) {
            foreach ($results as $i => $result) {
                if (!$result instanceof cashReportMenuItemInterface) {
                    unset($eventResult[$plugin][$i]);
                }
            }
        }

        $this->view->assign('reportsMenu', $eventResult);
    }
}
