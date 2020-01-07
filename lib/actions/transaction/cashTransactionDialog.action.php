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
     */
    public function runAction($params = null)
    {
        $transactionId = waRequest::get('transaction_id', 0, waRequest::TYPE_INT);
        $categoryType = waRequest::get('category_type', cashCategory::TYPE_INCOME, waRequest::TYPE_STRING_TRIM);
        $accountId = waRequest::get('account_id', 0, waRequest::TYPE_INT);

        /** @var cashTransaction $transaction */
        if ($transactionId) {
            $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($transactionId);
            kmwaAssert::instance($transaction, cashTransaction::class);
        } else {
            $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
            $transaction->setAccountId($accountId);
        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $accountDtos = cashDtoFromEntityFactory::fromEntities(
            cashAccountDto::class,
            cash()->getEntityRepository(cashAccount::class)->findAll()
        );
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAllByType($categoryType)
        );

        /**
         * UI in transaction dialog
         * @event backend_transaction_dialog
         *
         * @param cashEvent $event Event object with cashTransaction object (new or existing)
         * @return string HTML output
         */
        $event = new cashEvent(cashEventStorage::WA_BACKEND_TRANSACTION_DIALOG, $transaction);
        $eventResult = cash()->waDispatchEvent($event);

        $this->view->assign(
            [
                'transaction' => $transactionDto,
                'accounts' => $accountDtos,
                'categories' => $categoryDtos,
                'categoryType' => $categoryType,

                'backend_transaction_dialog' => $eventResult,
            ]
        );
    }
}
