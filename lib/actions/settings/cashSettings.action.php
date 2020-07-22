<?php

/**
 * Class cashSettingsAction
 */
class cashSettingsAction extends cashViewAction
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

        if (waRequest::getMethod() === 'post') {
            $settingsData = waRequest::post('shopscript_settings', waRequest::TYPE_ARRAY_TRIM, []);
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

        /** @var cashAccount $incomeAccount */
        $incomeAccount = cash()->getEntityRepository(cashAccount::class)->findById(
            $shopIntegration->getSettings()->getAccountId()
        );

        if ($shopIntegration->shopExists()) {
            $storefronts = cashHelper::getAllStorefronts() ?: [];
            $storefronts[] = 'backend';

            /** @var waWorkflowAction[] $actions */
            $actions = (new shopWorkflow())->getAllActions();
            $avg = round($shopIntegration->getShopAvgAmount(), 2);
        } else {
            $storefronts = [];
            $actions = [];
            $shopIntegration->getSettings()->setEnabled(false)->save();
            $avg = 0;
        }

        $this->view->assign(
            [
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'accounts' => $accountDtos,
                'shopScriptSettings' => $shopIntegration->getSettings(),
                'storefronts' => $storefronts,
                'actions' => $actions,
                'shopIsOld' => $shopIntegration->shopIsOld(),
                'avg' => sprintf(
                    '%s%s',
                    $avg,
                    $incomeAccount
                        ? cashCurrencyVO::fromWaCurrency($incomeAccount->getCurrency())->getSignHtml()
                        : cashCurrencyVO::fromWaCurrency(wa()->getLocale() === 'en_US' ? 'USD' : 'RUB')->getSignHtml()
                ),
            ]
        );
    }
}
