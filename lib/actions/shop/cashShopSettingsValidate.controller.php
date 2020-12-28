<?php

/**
 * Class cashShopSettingsValidateAction
 */
class cashShopSettingsValidateController extends cashJsonController
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (!cash()->getUser()->isAdmin()) {
            throw new kmwaForbiddenException();
        }
    }

    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function execute($params = null)
    {
        if (waRequest::getMethod() !== 'post') {
            return;
        }

        $shopIntegration = new cashShopIntegration();
        $settingsData = waRequest::post('shopscript_settings', waRequest::TYPE_ARRAY_TRIM, []);

        if (!(new cashShopIntegrationManager())->setup($shopIntegration, $settingsData)) {
            $this->errors = $shopIntegration->getSettings()->getErrors();
        }
    }
}
