<?php

/**
 * Class cashBackendImportListener
 */
class cashBackendImportListener extends waEventHandler
{
    /**
     * @param cashImportFileUploadedEvent $event
     *
     * @return cashImportResponseCsv
     */
    public function fileUploaded(&$event)
    {
        $csvImport = cashImportCsv::createNew($event->getSavePath().$event->getFile()->name, $event->getParams());

        return $csvImport->collectInfo();
    }
}
