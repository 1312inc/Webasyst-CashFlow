<?php

/**
 * Class cashImportCsvDeleteController
 */
class cashImportCsvDeleteController extends cashJsonController
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (!cash()->getUser()->canImport()) {
            throw new kmwaForbiddenException();
        }
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $id = waRequest::post('id', 0, waRequest::TYPE_INT);
        /** @var cashImport $import */
        $import = cash()->getEntityRepository(cashImport::class)->findById($id);
        kmwaAssert::instance($import, cashImport::class);

        $import->setIsArchived(1);
        cash()->getEntityPersister()->update($import);
    }
}
