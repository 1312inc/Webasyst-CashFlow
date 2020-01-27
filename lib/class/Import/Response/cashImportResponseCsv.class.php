<?php

/**
 * Class cashImportResponseCsv
 */
class cashImportResponseCsv implements cashImportFileUploadedEventResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getFileType()
    {
        return self::FILE_TYPE_CSV;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        $view = wa()->getView();
        $template = wa()->getAppPath('templates/include/import/csv.html', cashConfig::APP_ID);

        return $view->fetch($template);
    }

    /**
     * @inheritDoc
     */
    public function getIdentification()
    {
        return 'cash-csv';
    }
}
