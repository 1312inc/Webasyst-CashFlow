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

        $shopTransactionFactory = $integration->getTransactionFactory();
        try {
            $transaction = null;
            $createTransactionDto = new cashShopCreateTransactionDto($params);

            if (in_array($params['action_id'], $settings->getIncomeActions(), true)) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new income transaction for action %s', $params['action_id'])
                );

                $shopTransactionFactory->createTransactions($createTransactionDto, cashShopTransactionFactory::INCOME);
            } elseif (in_array($params['action_id'], $settings->getExpenseActions(), true)) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new expense transaction for action %s', $params['action_id'])
                );

                $shopTransactionFactory->createTransactions($createTransactionDto, cashShopTransactionFactory::EXPENSE);
            }

            if ($transaction instanceof cashTransaction) {
                if ($settings->isEnableForecast()) {
                    $integration->deleteForecastTransactionForDate(new DateTime());
                }

                $integration->saveTransactions($createTransactionDto);
            }
        } catch (Exception $ex) {
            cash()->getLogger()->error('Some error occurs on shop order transaction creation', $ex);
        }
    }
}
