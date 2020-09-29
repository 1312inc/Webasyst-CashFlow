<?php

/**
 * Class cashBackendSidebarAction
 */
class cashBackendSidebarAction extends cashViewAction
{
    /**
     * @param null|array $params
     *
     * @return mixed
     * @throws waException
     */
    public function runAction($params = null)
    {
        $accounts = cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact();
        $accountDtos = cashAccountDtoAssembler::createFromEntitiesWithStat(
            $accounts,
            wa()->getUser(),
            new DateTime(),
            new DateTime('1970-01-01')
        );

        $incomes = cash()->getEntityRepository(cashCategory::class)->findAllByTypeForContact(cashCategory::TYPE_INCOME);
        $incomeDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $incomes);

        $expenses = cash()->getEntityRepository(cashCategory::class)->findAllByTypeForContact(cashCategory::TYPE_EXPENSE);
        $expenseDtos = cashDtoFromEntityFactory::fromEntities(cashCategoryDto::class, $expenses);

        $importDtos = [];
        if (cash()->getUser()->canImport()) {
            $imports = cash()->getEntityRepository(cashImport::class)->findLastN(10);
            $importDtos = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
        }

        $hasTransfers = (bool) cash()->getModel(cashTransaction::class)->countTransfers(wa()->getUser());

        /**
         * UI in main sidebar
         *
         * @event backend_sidebar
         *
         * @param kmwaEventInterface $event Event object
         *
         * @return string HTML output
         */
        $event = new cashEvent(cashEventStorage::WA_BACKEND_SIDEBAR);
        $eventResult = cash()->waDispatchEvent($event);

        $shopStatic = '';
        if (wa()->appExists('shop')) {
            $shopStatic = wa()->getAppStaticUrl('shop');
        }

        $this->view->assign(
            [
                'accounts' => $accountDtos,
                'incomes' => $incomeDtos,
                'expenses' => $expenseDtos,
                'imports' => $importDtos,
                'backend_sidebar' => $eventResult,
                'hasTransfers' => $hasTransfers,
                'shopStatic' => $shopStatic,
            ]
        );
    }
}
