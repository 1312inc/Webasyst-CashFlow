<?php

/**
 * Class cashShopSettingsAction
 */
class cashShopSettingsAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     *
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActive();
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        $incomes = cash()->getEntityRepository(cashCategory::class)->findAllByType(cashCategory::TYPE_INCOME);
        $incomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $incomes);

        $expenses = cash()->getEntityRepository(cashCategory::class)->findAllByType(cashCategory::TYPE_EXPENSE);
        $expenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $expenses);

        $shopIntegration = new cashShopIntegration();

        $settingsData = waRequest::post('shopscript_settings', waRequest::TYPE_ARRAY_TRIM, []);
        $settings = $shopIntegration->getSettings();
        if (waRequest::getMethod() === 'post') {
            $settings
                ->load($settingsData)
                ->save();

            if ($settings->validate($settingsData)) {
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
            }
        }

        if ($settings->getAccountId()) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById(
                $settings->getAccountId()
            );
        } else {
            $account = cash()->getEntityRepository(cashAccount::class)->findFirst();
            $settings->setAccountId($account->getId());
        }

        $storefronts = [];
        $actions = [];
        $avg = 0;
        $shopCurrencyExists = true;
        if ($shopIntegration->shopExists()) {
            $storefronts = cashHelper::getAllStorefronts() ?: [];
            $storefronts[] = 'backend';

            /** @var waWorkflowAction[] $actions */
            $actions = (new shopWorkflow())->getAllActions();
            $avg = round($shopIntegration->getShopAvgAmount(), 2);
            $shopCurrencyExists = (bool)(new shopCurrencyModel())->getById($account->getCurrency());
            $dateBounds = $shopIntegration->getOrderBounds();
        } else {
            $settings->setEnabled(false)->save();
            $dateBounds = ['min' => '', 'max' => ''];
        }

        $accountCurrency = $account
            ? cashCurrencyVO::fromWaCurrency($account->getCurrency())
            : cashCurrencyVO::fromWaCurrency(wa()->getLocale() === 'en_US' ? 'USD' : 'RUB');

        $this->view->assign(
            [
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'accounts' => $accountDtos,
                'shopScriptSettings' => $settings,
                'storefronts' => $storefronts,
                'actions' => $actions,
                'shopIsOld' => $shopIntegration->shopIsOld(),
                'avg' => sprintf('%s%s', $avg, $accountCurrency->getSignHtml()),
                'accountCurrencySign' => $accountCurrency->getSignHtml(),
                'shopCurrencyExists' => $shopCurrencyExists,
                'ordersToImportCount' => $shopIntegration->countOrdersToProcess(),
                'errors' => $settings->getErrors(),
                'shopOrderDateBounds' => $dateBounds,
            ]
        );
    }
}
