<?php

/**
 * Class cashShopSettingsAction
 */
class cashShopSettingsAction extends cashViewAction
{
    /**
     * @throws kmwaForbiddenException
     * @throws waException
     */
    protected function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        if (!cash()->getUser()->isAdmin()) {
            throw new kmwaForbiddenException();
        }
    }

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
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        $accountDtos = cashDtoFromEntityFactory::fromEntities(cashAccountDto::class, $accounts);

        $incomes = cash()->getEntityRepository(cashCategory::class)->findAllByTypeForContact(cashCategory::TYPE_INCOME);
        $incomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $incomes);

        $expenses = cash()->getEntityRepository(cashCategory::class)->findAllByTypeForContact(
            cashCategory::TYPE_EXPENSE
        );
        $expenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $expenses);

        $shopIntegration = new cashShopIntegration();
        $settingsData = waRequest::post('shopscript_settings', [], waRequest::TYPE_ARRAY_TRIM);

        if (waRequest::getMethod() === 'post') {
            (new cashShopIntegrationManager())->setup($shopIntegration, $settingsData);
        }

        $settings = $shopIntegration->getSettings();

        if (!$settings->getAccountId()) {
            $account = cash()->getEntityRepository(cashAccount::class)->findFirstForContact();
        } else {
            $account = $settings->getAccount();
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
            $avg = round($shopIntegration->getShopAvgAmount($account->getCurrency()), 2);
            $shopCurrencyExists = (bool) (new shopCurrencyModel())->getById($account->getCurrency());
            $dateBounds = $shopIntegration->getOrderBounds();
        } else {
            if ($settings->isEnabled()) {
                $settings->setEnabled(false)->save();
            }
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
                'hasErrors' => !empty($settings->getErrors()),
                'errors' => $settings->getErrors(),
                'shopOrderDateBounds' => $dateBounds,
            ]
        );
    }
}
