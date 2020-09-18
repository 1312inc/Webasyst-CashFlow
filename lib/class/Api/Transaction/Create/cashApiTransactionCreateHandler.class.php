<?php

/**
 * Class cashApiTransactionCreateHandler
 */
class cashApiTransactionCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionCreateRequest $request
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
        $repeatingDto->end = [
            'after' => $request->repeating_end_after,
            'ondate' => $request->repeating_end_ondate,
        ];

        $factory = cash()->getEntityFactory(cashTransaction::class);
        $transaction = $factory->createNew();

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
            throw new kmwaForbiddenException(_w('You can not edit or add new transaction'));
        }

        if ($paramsDto->transfer) {
            $transferTransaction = $saver->createTransfer($transaction, $paramsDto);
        }

        $newTransactionIds = [];

        if ($request->is_repeating) {
            $repeatTransactionSaver = new cashRepeatingTransactionSaver();
            $transactionRepeater = new cashTransactionRepeater();

            $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                $transaction,
                $repeatingDto
            );

            if ($repeatingSaveResult->ok) {
                $newTransactions = $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                if ($newTransactions) {
                    foreach ($newTransactions as $newTransaction) {
                        $newTransactionIds[] = $newTransaction->getId();
                    }
                }
            }

            if (isset($transferTransaction)) {
                $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                    $transferTransaction,
                    $repeatingDto
                );

                $newTransactions = $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                if ($newTransactions) {
                    foreach ($newTransactions as $newTransaction) {
                        $newTransactionIds[] = $newTransaction->getId();
                    }
                }
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
