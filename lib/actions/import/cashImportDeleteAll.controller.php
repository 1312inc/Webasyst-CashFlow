<?php

/**
 * Class cashImportDeleteAllController
 */
class cashImportDeleteAllController extends cashJsonController
{
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
