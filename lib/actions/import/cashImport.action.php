<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends cashViewAction
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
     * @param null|array $params
     *
     * @return mixed
     */
    public function runAction($params = null)
    {
        $importDtos = [];
        if (cash()->getUser()->canImport()) {
            $imports = cash()->getEntityRepository(cashImport::class)->findLastN(10);
            $importDtos = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
        }

        $this->view->assign(['imports' => $importDtos]);

        /**
         * @event backend_imports_menu_item
         *
         * @param cashImportMenuItemEvent $event Event object
         *
         * @return cashImportMenuItemInterface Menu object
         */
        $event = new cashImportMenuItemEvent(cashEventStorage::WA_IMPORTS_MENU_ITEM);
        $eventResult = cash()->waDispatchEvent($event);
        foreach ($eventResult as $plugin => $results) {
            foreach ($results as $i => $result) {
                if (!$result instanceof cashImportMenuItemInterface) {
                    unset($eventResult[$plugin][$i]);
                }
            }
        }

        $importHtml = 'import html';
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

            $event = new cashImportHandlerParamsEvent(cashEventStorage::WA_IMPORTS_HANDLE);
            $eventResultHandlers = cash()->waDispatchEvent($event);
            foreach ($eventResultHandlers as $plugin => $results) {
                foreach ($results as $i => $result) {
                    if ($result instanceof cashImportHandlerInterface && $result->canHandle($handler)) {
                        $importHtml = $result->handle($handlerParams);
                        break;
                    }
                }
            }
        }

        $this->view->assign([
            'importsMenu' => $eventResult,
            'current_menu' => $handler,
            'import_html' => $importHtml,
        ]);
    }
}
