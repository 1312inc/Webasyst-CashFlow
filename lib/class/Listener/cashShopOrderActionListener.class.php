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

        $manager = new cashShopTransactionManager(new cashShopSettings());

        if (!$manager->getSettings()->isEnabled()) {
            return;
        }

        try {
            if (in_array($params['action_id'], $manager->getSettings()->getIncomeActions())) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new income transaction for action %s', $params['action_id'])
                );

                $manager->createTransaction($params['order_id'], cashShopTransactionManager::INCOME);
            } elseif (in_array($params['action_id'], $manager->getSettings()->getExpenseActions())) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new expense transaction for action %s', $params['action_id'])
                );

                $manager->createTransaction($params['order_id'], cashShopTransactionManager::EXPENSE, $params);
            }
        } catch (Exception $ex) {
            cash()->getLogger()->error('Some error occurs on shop order transaction creation', $ex);
        }
    }
}
