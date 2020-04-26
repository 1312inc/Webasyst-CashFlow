<?php

/**
 * Class cashShopOrderActionListener
 */
class cashShopOrderActionListener extends waEventHandler
{
    /**
     * @param $params
     */
    public function execute(&$params)
    {
        if (!isset($params['action_id'])) {
            return;
        }

        $integration = new cashShopIntegration();
        $settings = $integration->getSettings();

        if (!$settings->isEnabled()) {
            return;
        }

        $manager = $integration->getTransactionManager();
        try {
            $transaction = null;

            if (in_array($params['action_id'], $settings->getIncomeActions())) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new income transaction for action %s', $params['action_id'])
                );

                $transaction = $manager->createTransaction(
                    $params['order_id'],
                    cashShopTransactionManager::INCOME
                );
            } elseif (in_array(
                $params['action_id'],
                $settings->getExpenseActions()
            )) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new expense transaction for action %s', $params['action_id'])
                );

                $transaction = $manager->createTransaction(
                    $params['order_id'],
                    cashShopTransactionManager::EXPENSE,
                    $params
                );
            }

            if ($transaction instanceof cashTransaction) {
                $manager->saveTransaction($transaction, $params);
            }
        } catch (Exception $ex) {
            cash()->getLogger()->error('Some error occurs on shop order transaction creation', $ex);
        }
    }
}
