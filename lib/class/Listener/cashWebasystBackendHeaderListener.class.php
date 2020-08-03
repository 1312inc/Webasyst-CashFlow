<?php

/**
 * Class cashWebasystBackendHeaderListener
 */
class cashWebasystBackendHeaderListener extends waEventHandler
{
    /**
     * @param $params
     */
    public function execute(&$params)
    {
        try {
            $shopIntegration = new cashShopIntegration();
            if ($shopIntegration->shopExists() && $shopIntegration->getSettings()->isEnableForecast()) {
                $date = DateTime::createFromFormat('Y-m-d|', date('Y-m-d'));
                $shopIntegration->deleteForecastTransactionBeforeDate($date);

                // обновить сумму транзакции или заного сгенерить транзакции
                $shopIntegration->actualizeForecastTransaction();
            }
        } catch (Exception $ex) {
            cash()->getLogger()->debug(
                sprintf(
                    "Error on delete previous shop forecast transaction before date %s: %s.\n%s",
                    isset($date) ? $date->format('Y-m-d') : '???',
                    $ex->getMessage(),
                    $ex->getTraceAsString()
                )
            );
        }
    }
}
