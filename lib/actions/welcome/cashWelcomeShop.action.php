<?php

/**
 * Class cashWelcomeShopAction
 */
class cashWelcomeShopAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     */
    public function runAction($params = null)
    {
        $shopWelcome = new cashShopWelcome();
        if (waRequest::getMethod() === 'post') {
            if (waRequest::post('skip', 0, waRequest::TYPE_INT) === 1) {
                $shopWelcome->setWelcomePassed(wa()->getUser());

                return;
            }
        }

        $this->view->assign(
            [
                'orders_to_import_count' => $shopWelcome->countOrdersToProcess(),
                'backend_url' => wa()->getAppUrl(cashConfig::APP_ID),
            ]
        );
    }
}
