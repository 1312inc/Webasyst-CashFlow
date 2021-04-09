<?php

/**
 * Class cashApiTransactionCreateHandler
 */
class cashApiTransactionCreateHandler implements cashApiHandlerInterface
{
    /**
     * @var cashApiTransactionResponseDtoAssembler
     */
    private $transactionResponseDtoAssembler;

    public function __construct()
    {
        $this->transactionResponseDtoAssembler = new cashApiTransactionResponseDtoAssembler();
    }

    /**
     * @param cashApiTransactionCreateRequest $request
     *
     * @return array<cashApiTransactionResponseDto>
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

        if ($paramsDto->transfer) {
            if (!cash()->getContactRights()->canAccessTransfers(wa()->getUser())) {
                throw new kmwaForbiddenException(_w('You are not allowed to create transfer transactions'));
            }

            $transferTransaction = $saver->createTransfer($transaction, $paramsDto);

            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transferTransaction)) {
                throw new kmwaForbiddenException(
                    _w('You are now allowed to add/edit transfer transactions with the specified set of parameters')
                );
            }

            if ($transferTransaction) {
                $saver->addToPersist($transferTransaction);
            }
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(
                _w('You are now allowed to add/edit transactions with the specified set of parameters')
            );
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
                $newRepeatedTransactionIds = $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                if ($newRepeatedTransactionIds) {
                    $firstId = reset($newRepeatedTransactionIds);
                    $newTransactionIds[$firstId] = $newRepeatedTransactionIds;
                }
            }

            if (isset($transferTransaction)) {
                $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                    $transferTransaction,
                    $repeatingDto
                );

                if ($repeatingSaveResult->ok) {
                    $newRepeatedTransactionIds = $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                    if ($newRepeatedTransactionIds) {
                        $firstId = reset($newRepeatedTransactionIds);
                        $newTransactionIds[$firstId] = $newRepeatedTransactionIds;
                    }
                }
            }
        } else {
            $saver->addToPersist($transaction);
            $saved = $saver->persistTransactions();
            foreach ($saved as $item) {
                $newTransactionIds[$item->getId()][] = (int) $item->getId();
            }
        }

        $transactionModel = cash()->getModel(cashTransaction::class);
        $data = $transactionModel->getById(array_keys($newTransactionIds));
        $response = [];
        foreach ($data as $datum) {
            $dto = $this->transactionResponseDtoAssembler->fromData($datum);
            $dto->affected_transactions = count($newTransactionIds[$dto->id]);
            $dto->affected_transaction_ids = $newTransactionIds[$dto->id];
            $response[] = $dto;
        }

        return $response;
    }
}
