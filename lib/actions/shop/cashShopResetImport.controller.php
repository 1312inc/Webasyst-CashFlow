<?php

/**
 * Class cashShopResetImportController
 */
class cashShopResetImportController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $shopIntegration = new cashShopIntegration();
        $shopIntegration->getSettings()->resetSettings();
    }
}
