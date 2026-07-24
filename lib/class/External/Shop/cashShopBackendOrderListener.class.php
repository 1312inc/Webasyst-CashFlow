<?php

final class cashShopBackendOrderListener extends waEventHandler
{
    /**
     * @param $params
     */
    public function execute(&$params)
    {
        if (!wa()->getUser()->isAdmin(cashConfig::APP_ID)) {
            return [];
        }

        $transactions = cash()->getModel()->query('
                SELECT cc.*, ct.amount, ca.currency, IF (ct.`date` > s:current_date, 1, 0) upcoming FROM cash_transaction ct
                LEFT JOIN cash_account ca ON ca.id = ct.account_id
                LEFT JOIN cash_category cc ON cc.id = ct.category_id
                WHERE ct.external_source = s:external_source 
                AND external_id = i:external_id
                AND ct.is_archived = 0
                ORDER BY ct.`date`
            ', [
                'current_date' => date('Y-m-d'),
                'external_source' => 'shop',
                'external_id' => (int) $params['id']
        ])->fetchAll();

        try {
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
            $view = new waSmarty3View(wa());
            $view->assign(['info' => $dto]);

            return ['aux_info' => $view->fetch($template)];
        } catch (Exception $ex) {
            cash()->getLogger()
                ->error('Some error occurs on shop backend order hook', $ex);
        }

        return [];
    }
}
