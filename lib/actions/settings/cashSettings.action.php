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

        $shopScriptSettings = new cashShopScriptSettings();

        if (waRequest::getMethod() === 'post') {
            $settingsData = waRequest::post('shopscript_settings', waRequest::TYPE_ARRAY_TRIM, []);
            $shopScriptSettings->load($settingsData)->save();
        }

        if (!wa()->appExists('shop')) {
            $storefronts = [];
            $shopScriptSettings->setEnabled(false)->save();
        } else {
            $storefronts = shopStorefrontList::getAllStorefronts() ?: [];
        }

        $this->view->assign(
            [
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'accounts' => $accountDtos,
                'shopScriptSettings' => $shopScriptSettings,
                'storefronts' => $storefronts,
            ]
        );
    }
}
