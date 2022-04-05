<?php

final class cashShopIntegrationManager
{
    /**
     * @param array<string, mixed> $settingsData
     */
    public function setup(cashShopIntegration $shopIntegration, array $settingsData): bool
    {
        if (isset($settingsData['accounts_payment'])) {
            $settingsData['accountIdByPayment'] = array_filter(
                array_combine(
                    $settingsData['accounts_payment']['payment_method'],
                    $settingsData['accounts_payment']['account']
                )
            );
            unset($settingsData['accounts_payment']);
        }

        $settings = $shopIntegration->getSettings();

        try {
            $settings->load($settingsData);
            if ($settingsData && !$settings->validate($settingsData)) {
                return false;
            }

            $settings->save();

            switch (true) {
                case $settings->isTurnedOff():
                    $shopIntegration->turnedOff();
                    break;

                case $settings->isTurnedOn():
                    $shopIntegration->turnedOn();
                    break;

                case $settings->forecastTurnedOff():
                    $shopIntegration->disableForecast();
                    break;

                case $settings->forecastTurnedOn():
                    $shopIntegration->enableForecast();
                    break;

                case $settings->forecastTypeChanged()
                    || $settings->forecastAccountChanged()
                    || $settings->forecastCategoryIncomeChanged():
                    $shopIntegration->changeForecastType();
                    break;
            }

            if (!$settings->getAccountId()) {
                $account = cash()->getEntityRepository(cashAccount::class)->findFirstForContact();
                $settings->setAccountId($account->getId());
            }

            return true;
        } catch (Exception $exception) {
            cash()->getLogger()->error('Error on setup shop integration', $exception);
        }

        return false;
    }
}
