<?php

/**
 * Class cashImportCsvDownloadErrorsController
 */
class cashImportCsvDownloadErrorsController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $id = waRequest::get('id', '', waRequest::TYPE_INT);

        try {
            /** @var cashImport $import */
            $import = cash()->getEntityRepository(cashImport::class)->findById($id);
            kmwaAssert::instance($import, cashImport::class);

            $errorLog = sprintf('%s/cash/import_%s_errors.log', waConfig::get('wa_path_log'), $import->getId());
            if (!file_exists($errorLog)) {
                $errorLog = wa()->getTempPath(sprintf('export_csv_errors/%d.txt', $import->getId()));
                $file = fopen($errorLog, 'wb+');
                fclose($file);
            }
            waFiles::readFile($errorLog, sprintf('export_csv_errors_%d.txt', $import->getId()));
        } catch (Exception $exception) {

        }
    }
}
