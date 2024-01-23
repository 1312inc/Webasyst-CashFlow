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
        $plugins = [];
        $importDtos = [];
        if (cash()->getUser()->canImport()) {
            $imports = cash()->getEntityRepository(cashImport::class)->findLastN(10);
            $importDtos = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
        }
        foreach ((array) $this->getConfig()->getPlugins() as $_plugin) {
            if (!empty($_plugin['import_api'])) {
                $plugins[] = $_plugin;
            }
        }

        $this->view->assign([
            'imports' => $importDtos,
            'plugins' => $plugins,
        ]);
    }
}
