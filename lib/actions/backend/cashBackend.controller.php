<?php

/**
 * Class cashBackendController
 */
class cashBackendController extends waViewController
{
    public function execute()
    {
        $welcome = waRequest::get('welcome') === 'shop'
            || ((new cashShopIntegration())->shopExists() && !(new cashShopWelcome())->welcomePassed(wa()->getUser()));

        if ($welcome) {
            $this->setLayout(new cashWelcomeLayout());
        } else {
            $this->setLayout(new cashDefaultLayout());
        }
    }
}
