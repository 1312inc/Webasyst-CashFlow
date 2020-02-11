<?php

/**
 * Class cashCashBackend_importFile_uploadedHandler
 */
class cashCashBackend_importFile_uploadedHandler extends waEventHandler
{
    /**
     * @param cashImportFileUploadedEvent $event
     *
     * @return cashImportResponseCsv
     */
    public function execute(&$event)
    {
        $csvImport = cashImportCsv::createNew($event->getSavePath().$event->getFile()->name, $event->getParams());

        return $csvImport->collectInfo();
    }
}
