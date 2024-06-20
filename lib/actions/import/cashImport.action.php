<?php

/**
 * Class cashImportAction
 */
class cashImportAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        $importDtos = [];
        if (cash()->getUser()->canImport()) {
            $imports = cash()->getEntityRepository(cashImport::class)->findLastN(10);
            $importDtos = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
        }

        $this->view->assign([
            'imports' => $importDtos,
        ]);
    }
}
