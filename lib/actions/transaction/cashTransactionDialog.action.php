<?php

/**
 * Class cashTransactionDialogAction
 */
class cashTransactionDialogAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws waException
     * @throws kmwaLogicException
     * @throws kmwaAssertException
     * @throws kmwaNotFoundException
     */
    public function runAction($params = null)
    {
        $transactionId = waRequest::get('transaction_id', 0, waRequest::TYPE_INT);
        $categoryType = waRequest::get('category_type', cashCategory::TYPE_INCOME, waRequest::TYPE_STRING_TRIM);
        $filterType = waRequest::get('filter_type', cashCategory::TYPE_INCOME, waRequest::TYPE_STRING_TRIM);
        $filterId = waRequest::get('filter_id', '', waRequest::TYPE_STRING_TRIM);

        $filterDto = new cashTransactionPageFilterDto($filterType, $filterId);

        /** @var cashTransaction $transaction */
        if ($transactionId) {
            $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($transactionId);
            kmwaAssert::instance($transaction, cashTransaction::class);
            $categoryType = $transaction->getCategoryType();

            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You can not edit transaction'));
            }
        } else {
            $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
            if ($filterDto->type === cashTransactionPageFilterDto::FILTER_ACCOUNT) {
                $transaction->setAccountId($filterDto->id);
            } else {
                $transaction->setCategoryId($filterDto->id);
            }

            if (!cash()->getContactRights()->canAddTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You can not add new transaction'));
            }
        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $accountDtos = cashDtoFromEntityFactory::fromEntities(
            cashAccountDto::class,
            cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact()
        );
        $repeatingTransactionDto = $transaction->getRepeatingTransaction() instanceof cashRepeatingTransaction
            ? (new cashRepeatingTransactionDtoAssembler())->createFromEntity(
                $transaction->getRepeatingTransaction(),
                $transaction
            )
            : new cashRepeatingTransactionDto([]);

        /** @var cashCategoryRepository $categoryRep */
        $categoryRep = cash()->getEntityRepository(cashCategory::class);
        if ($categoryType === cashCategory::TYPE_INCOME) {
            $categoryDtos = cashDtoFromEntityFactory::fromEntities(
                cashCategoryDto::class,
                $categoryRep->findAllIncomeForContact()
            );
        } elseif ($categoryType === cashCategory::TYPE_EXPENSE) {
            $categoryDtos = cashDtoFromEntityFactory::fromEntities(
                cashCategoryDto::class,
                $categoryRep->findAllExpense()
            );
        } else {
            $categoryDtos = cashDtoFromEntityFactory::fromEntities(
                cashCategoryDto::class,
                $categoryRep->findAllActiveForContact()
            );
        }

        /**
         * UI in transaction dialog
         *
         * @event backend_transaction_dialog
         *
         * @param cashEvent $event Event object with cashTransaction object (new or existing)
         *
         * @return string HTML output
         */
        $event = new cashEvent(cashEventStorage::WA_BACKEND_TRANSACTION_DIALOG, $transaction);
        $eventResult = cash()->waDispatchEvent($event);

        $this->view->assign(
            [
                'transaction' => $transactionDto,
                'repeatingTransaction' => $repeatingTransactionDto,
                'accounts' => $accountDtos,
                'categories' => $categoryDtos,
                'categoryType' => $categoryType,
                'filter' => $filterDto,

                'backend_transaction_dialog' => $eventResult,
            ]
        );
    }
}
