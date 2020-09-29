<?php

/**
 * Class cashShopResetImportController
 */
class cashShopResetImportController extends cashJsonController
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
     * @throws waException
     */
    public function execute($params = null)
    {
        $shopIntegration = new cashShopIntegration();

        $code = wa()->getStorage()->get('cash.shop_integration_reset_code');
        if (waRequest::getMethod() === 'post') {
            if ($code === waRequest::post('code', '')) {
                wa()->getStorage()->del('cash.shop_integration_reset_code');

                $shopIntegration->getSettings()->resetSettings();
                $shopIntegration->disableForecast();
                $shopIntegration->deleteAllShopTransactions();
            } else {
                $this->setError(_w('Invalid code'));
            }
        } else {
            $this->setError('Invalid method');
        }
    }
}
