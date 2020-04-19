<?php

/**
 * Class cashBackendTransactionsExport
 */
class cashBackendTransactionsExport extends waEventHandler
{
    /**
     * @param cashExportEvent $event
     *
     * @return string
     */
    public function execute(&$event)
    {
        $anchor = _w('CSV');
        $urlParams = [
            'settings' => [
                'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $event->getEndDate()->format('Y-m-d H:i:s'),
                'entity_type' => $event->getObject()->type,
                'entity_id' => $event->getObject()->identifier,
            ],
        ];
        $url = sprintf('?module=export&action=csv&%s', http_build_query($urlParams));

        return <<<HTML
<a href="{$url}"><i class="icon16 c-table"></i>{$anchor}</a>
HTML;
    }
}
