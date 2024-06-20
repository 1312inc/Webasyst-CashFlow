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
        $provider = waRequest::request('provider', 'csv', waRequest::TYPE_STRING_TRIM);
        $imports = cash()->getEntityRepository(cashImport::class)->findAllActive($provider);

        foreach ($imports as $import) {
            $import->setIsArchived(1);
            cash()->getEntityPersister()->update($import);
        }
    }
}
