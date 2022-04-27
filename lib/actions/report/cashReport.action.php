<?php

/**
 * Class cashReportAction
 */
class cashReportAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        } elseif (!waRequest::isXMLHttpRequest() && wa()->whichUI() === '1.3') {
            $this->redirect(wa()->getAppUrl());
        }

        parent::preExecute();
    }

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

        $reportHtml = 'report html';
        $handler = waRequest::param('handler', '', waRequest::TYPE_STRING_TRIM);
        $reportParams = waRequest::param('params', '', waRequest::TYPE_STRING_TRIM);
        if (!$handler && $eventResult) {
            $firstHandler = reset($eventResult);
            $handler = $firstHandler[0]->getIdentifier();
        }

        if ($handler) {
            $reportParams = $reportParams ? explode('&', $reportParams) : [];
            $handlerParams = [];
            foreach ($reportParams as $reportParam) {
                $keyValue = explode('=', $reportParam);
                $handlerParams[$keyValue[0]] = null;
                if (count($keyValue) === 2) {
                    $handlerParams[$keyValue[0]] = $keyValue[1];
                }
            }

            $event = new cashReportHandlerParamsEvent(cashEventStorage::WA_REPORTS_HANDLE);
            $eventResultHandlers = cash()->waDispatchEvent($event);
            foreach ($eventResultHandlers as $plugin => $results) {
                foreach ($results as $i => $result) {
                    if ($result instanceof cashReportHandlerInterface && $result->canHandle($handler)) {
                        $reportHtml = $result->handle($handlerParams);
                        break;
                    }
                }
            }
        }

        $this->view->assign([
            'reportsMenu' => $eventResult,
            'current_menu' => $handler,
            'report_html' => $reportHtml,
        ]);
    }
}
