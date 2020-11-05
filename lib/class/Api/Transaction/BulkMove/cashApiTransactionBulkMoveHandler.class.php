<?php

/**
 * Class cashApiTransactionBulkMoveHandler
 */
class cashApiTransactionBulkMoveHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionBulkMoveRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->ids);
        if (!$transactions) {
            return [];
        }

        $saver = new cashTransactionSaver();
        $updateData = [];
        $response = [];

        $account = null;
        if ($request->account_id) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById($request->account_id);
            kmwaAssert::instance($account, cashAccount::class);

            if (!cash()->getContactRights()->hasMinimumAccessToAccount(wa()->getUser(), $account->getId())) {
                throw new kmwaForbiddenException(_w('You have no access to this category'));
            }

            $updateData['account_id'] = $account->getId();
        }

        if ($request->category_id) {
            /** @var cashAccount $category */
            $category = cash()->getEntityRepository(cashCategory::class)->findById($request->category_id);
            kmwaAssert::instance($category, cashCategory::class);

            if (!cash()->getContactRights()->hasMinimumAccessToCategory(wa()->getUser(), $category->getId())) {
                throw new kmwaForbiddenException(_w('You have no access to this category'));
            }

            $updateData['category_id'] = $category->getId();
        }


        if (!$updateData['account_id'] && !$updateData['category_id']) {
            throw new kmwaRuntimeException(_w('No valid category'));
        }

        $fields = cash()->getModel(cashTransaction::class)->getMetadata();
        $params = new cashTransactionSaveParamsDto();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to edit this transaction'));
            }

            $transactionData = cash()->getHydrator()->extract($transaction, [], $fields);
            $saveData = array_merge($transactionData, $updateData);
            $transaction = $saver->saveFromArray($transaction, $saveData, $params);
            if ($transaction) {
                $response[] = cashApiTransactionResponseDtoAssembler::generateResponseFromEntity($transaction);
            }
        }

        return $response;
    }
}
