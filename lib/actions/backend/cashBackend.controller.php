<?php

/**
 * Class cashBackendController
 */
class cashBackendController extends waViewController
{
    public function execute()
    {
        $shopIntegration = new cashShopIntegration();
        $welcome = waRequest::get('welcome') === 'shop'
            || ($shopIntegration->shopExists()
                && !(new cashShopWelcome($shopIntegration))->welcomePassed(wa()->getUser()));

        if ($welcome) {
            $this->setLayout(new cashWelcomeLayout());
        } else {
            $this->setLayout(new cashDefaultLayout());
        }
    }
}
