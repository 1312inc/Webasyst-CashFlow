<?php

/**
 * Class cashImportFactory
 */
class cashImportFactory extends cashBaseFactory
{
    /**
     * @return cashImport
     */
    public function createNew()
    {
        return (new cashImport())->setContactId(wa()->getUser()->getId());
    }
}
