<?php

/**
 * Class cashBackend_importFile_uploadedHandler
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
       return new cashImportResponseCsv();
    }
}
