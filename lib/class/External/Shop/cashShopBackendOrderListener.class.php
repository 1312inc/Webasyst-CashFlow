<?php

final class cashShopBackendOrderListener extends waEventHandler
{
    /**
     * @param $params
     */
    public function execute(&$params)
    {
        $integration = new cashShopIntegration();
        $settings = $integration->getSettings();

        if (!$settings->isEnabled()) {
            return [];
        }

        if (!wa()->getUser()->isAdmin(cashConfig::APP_ID)) {
            return [];
        }

        $transactions = cash()->getEntityRepository(cashTransaction::class)
            ->findAllByExternalSourceAndId('shop', (int) $params['id']);

        if (!$transactions) {
            return [];
        }

        try {
            $view = new waSmarty3View(wa());

            $dto = cashShopBackendOrderDto::createFromTransactions(
                $transactions,
                sprintf(
                    '%sexternal/shop/order/%s/',
                    wa()->getAppUrl(cashConfig::APP_ID),
                    $params['id']
                )
            );

            $template = wa()->getAppPath(
                sprintf(
                    'templates/include/shop%s/backend_order.aux_info.html',
                    wa()->whichUI('shop') === '1.3' ? '-legacy' : ''
                ),
                cashConfig::APP_ID
            );

            $view->assign(['info' => $dto]);

            return ['aux_info' => $view->fetch($template)];
        } catch (Exception $ex) {
            cash()->getLogger()
                ->error('Some error occurs on shop backend order hook', $ex);
        }

        return [];
    }
}
