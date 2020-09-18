<?php

/**
 * Class cashApiTransactionUpdateHandler
 */
class cashApiTransactionUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionUpdateRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $repeatingDto = new cashRepeatingTransactionSettingsDto([]);
        $repeatingDto->end_type = $request->repeating_end_type;
        $repeatingDto->interval = $request->repeating_interval;
        $repeatingDto->frequency = $request->repeating_frequency;
        $repeatingDto->apply_to_all_in_future = $request->apply_to_all_in_future;
        $repeatingDto->end = [
            'after' => $request->repeating_end_after,
            'ondate' => $request->repeating_end_ondate,
        ];

        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($request->id);
        if (!$transaction) {
            throw new kmwaNotFoundException(_w('No transaction'));
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not edit transaction'));
        }

        $paramsDto = new cashTransactionSaveParamsDto();
        if ($request->transfer_account_id) {
            $paramsDto->transfer = [
                'account_id' => $request->transfer_account_id,
                'incoming_amount' => $request->transfer_incoming_amount,
            ];
        }

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)
            ->findById($request->category_id);
        if (!$category) {
            throw new kmwaNotFoundException(_w('Category not found'));
        }

        $account = cash()->getEntityRepository(cashAccount::class)
            ->findById($request->account_id);
        if (!$account) {
            throw new kmwaNotFoundException(_w('Account not found'));
        }

        $paramsDto->categoryType = $category->getType();

        if (empty($request->contractor_contact_id) && !empty($request->contractor)) {
            $newContractor = new waContact();
            $newContractor->set('name', $request->contractor);
            $newContractor->save();
            $request->contractor_contact_id = $newContractor->getId();
        }

        $data = (array) $request;
        $saver = new cashTransactionSaver();
        if (!$saver->populateFromArray($transaction, $data, $paramsDto)) {
            throw new kmwaRuntimeException($saver->getError());
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not edit transaction'));
        }

        $newTransactionIds = [];

        if ($request->is_repeating || $repeatingDto->apply_to_all_in_future) {
            /** @var cashRepeatingTransaction $repeatingTransaction */
            $repeatingTransaction = $transaction->getRepeatingTransaction();
            kmwaAssert::instance($repeatingTransaction, cashRepeatingTransaction::class);

            $repeatingTransaction
                ->setAccountId($transaction->getAccountId())
                ->setCategoryId($transaction->getCategoryId())
                ->setDescription($transaction->getDescription())
                ->setAmount($transaction->getAmount())
                ->setContractorContactId($transaction->getContractorContactId());
            $saver->addToPersist($repeatingTransaction);

            $transactions = cash()->getEntityRepository(cashTransaction::class)->findAllByRepeatingIdAndAfterDate(
                $repeatingTransaction->getId(),
                $transaction->getDate()
            );
            foreach ($transactions as $t) {
                $t->setAccountId($transaction->getAccountId())
                    ->setCategoryId($transaction->getCategoryId())
                    ->setDescription($transaction->getDescription())
                    ->setAmount($transaction->getAmount())
                    ->setContractorContactId($transaction->getContractorContactId());

                $saver->addToPersist($t);
            }

            $saved = $saver->persistTransactions();
            foreach ($saved as $item) {
                $newTransactionIds[$item->getId()] = true;
            }
        } else {
            $saver->addToPersist($transaction);
            $saved = $saver->persistTransactions();
            foreach ($saved as $item) {
                $newTransactionIds[] = $item->getId();
            }
        }

        $transactionModel = cash()->getModel(cashTransaction::class);
        $data = $transactionModel->getAllIteratorByIds($newTransactionIds);
        $response = [];
        foreach (cashApiTransactionResponseDtoAssembler::fromModelIterator($data) as $item) {
            $response[] = $item;
        }

        return $response;
    }
}
