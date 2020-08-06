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
        if (waRequest::getMethod() === 'post' && $shopIntegration->getSettings()->validate($settingsData)) {
            $shopIntegration->getSettings()
                ->load($settingsData)
                ->save();

            switch (true) {
                case $shopIntegration->getSettings()->isTurnedOff():
                    $shopIntegration->turnedOff();
                    break;

                case $shopIntegration->getSettings()->isTurnedOn():
                    $shopIntegration->turnedOn();
                    break;

                case $shopIntegration->getSettings()->forecastTurnedOff():
                    $shopIntegration->disableForecast();
                    break;

                case $shopIntegration->getSettings()->forecastTurnedOn():
                    $shopIntegration->enableForecast();
                    break;

                case $shopIntegration->getSettings()->forecastTypeChanged()
                    || $shopIntegration->getSettings()->forecastAccountChanged()
                    || $shopIntegration->getSettings()->forecastCategoryIncomeChanged():
                    $shopIntegration->changeForecastType();
                    break;
            }
        }

        if ($shopIntegration->getSettings()->getAccountId()) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById(
                $shopIntegration->getSettings()->getAccountId()
            );
        } else {
            $account = cash()->getEntityRepository(cashAccount::class)->findFirst();
            $shopIntegration->getSettings()->setAccountId($account->getId());
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
            $shopIntegration->getSettings()->setEnabled(false)->save();
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
                'shopScriptSettings' => $shopIntegration->getSettings(),
                'storefronts' => $storefronts,
                'actions' => $actions,
                'shopIsOld' => $shopIntegration->shopIsOld(),
                'avg' => sprintf('%s%s', $avg, $accountCurrency->getSignHtml()),
                'accountCurrencySign' => $accountCurrency->getSignHtml(),
                'shopCurrencyExists' => $shopCurrencyExists,
                'ordersToImportCount' => $shopIntegration->countOrdersToProcess(),
                'errors' => $shopIntegration->getSettings()->getErrors(),
                'shopOrderDateBounds' => $dateBounds,
            ]
        );
    }
}
