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
            $createTransactionDto = new cashShopCreateTransactionDto($params);

            if ($createTransactionDto->order->paid_date) {
                if (in_array($params['action_id'], $settings->getIncomeActions(), true)) {
                    cash()->getLogger()->debug(
                        sprintf('Okay, lets create new income transaction for action %s', $params['action_id'])
                    );

                    $shopTransactionFactory->createIncomeTransaction($createTransactionDto);
                } elseif (in_array($params['action_id'], $settings->getExpenseActions(), true)) {
                    cash()->getLogger()->debug(
                        sprintf('Okay, lets create new expense transaction for action %s', $params['action_id'])
                    );

                    $shopTransactionFactory->createExpenseTransaction($createTransactionDto);
                }
            } else {
                cash()->getLogger()->log(
                    sprintf(
                        'No paid date in order %s. Cant create transaction!',
                        $createTransactionDto->params['order_id']
                    )
                );
            }

            if ($createTransactionDto->mainTransaction instanceof cashTransaction) {
                if ($settings->isEnableForecast()) {
                    $integration->deleteForecastTransactionBeforeDate(new DateTime(), true);
                }

                $integration->saveTransactions($createTransactionDto);
            }
        } catch (Exception $ex) {
            cash()->getLogger()->error('Some error occurs on shop order transaction creation', $ex);
        }
    }
}
