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
            if ($settings->getAccountId() < 0) {
                $currencies = (array) wa('shop')->getConfig()->getCurrencies();
                foreach ($currencies as $currency) {
                    if (ifset($currency, 'is_primary', null)) {
                        break;
                    }
                }
                if (empty($currency)) {
                    $currency = reset($currencies);
                }

                /** @var cashAccount $account */
                $account = cash()->getEntityFactory(cashAccount::class)->createNew();
                $account->setName(_wp('Online store'))
                    ->setCurrency(ifset($currency, 'code', ''))
                    ->setDescription('')
                    ->setIcon('')
                    ->setIsImaginary(1)
                    ->setCustomerContactId(wa()->getUser()->getId());
                cash()->getEntityPersister()->save($account);
                $settings->setAccountId($account->getId());
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
