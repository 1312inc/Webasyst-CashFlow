<?php

/**
 * Class cashImportDeleteAllController
 */
class cashImportDeleteAllController extends cashJsonController
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
        $imports = cash()->getEntityRepository(cashImport::class)->findAllActive();

        foreach ($imports as $import) {
            $import->setIsArchived(1);
            cash()->getEntityPersister()->update($import);
        }
    }
}
