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

                case $shopIntegration->getSettings()->forecastTypeChanged():
                    $shopIntegration->changeForecastType();
                    break;
            }
        }

        if ($shopIntegration->shopExists()) {
            $storefronts = shopStorefrontList::getAllStorefronts() ?: [];
            $storefronts[] = 'backend';

            /** @var waWorkflowAction[] $actions */
            $actions = (new shopWorkflow())->getAllActions();
        } else {
            $storefronts = [];
            $actions = [];
            $shopIntegration->getSettings()->setEnabled(false)->save();
        }

        $this->view->assign(
            [
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'accounts' => $accountDtos,
                'shopScriptSettings' => $shopIntegration->getSettings(),
                'storefronts' => $storefronts,
                'actions' => $actions,
            ]
        );
    }
}
