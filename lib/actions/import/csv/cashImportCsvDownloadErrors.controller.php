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

            $path = wa()->getTempPath(sprintf('export_csv_errors/%d.txt', $import->getId()));
            $file = fopen($path, 'wb+');
            foreach ($import->getErrors() as $error) {
                fwrite($file, $error.PHP_EOL);
            }
            fclose($file);

            waFiles::readFile($path, sprintf('export_csv_errors_%d.txt', $import->getId()));
        } catch (Exception $exception) {

        }
    }
}
