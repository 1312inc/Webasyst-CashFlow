<?php

/**
 * @todo: test & refactor
 *
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
        $repeatingDto->end_type = $request->getRepeatingEndType();
        $repeatingDto->interval = $request->getRepeatingInterval();
        $repeatingDto->frequency = $request->getRepeatingFrequency();
        $repeatingDto->end = [
            'after' => $request->getRepeatingEndAfter(),
            'ondate' => $request->getRepeatingEndOndate() ? $request->getRepeatingEndOndate()->format('Y-m-d') : '',
        ];

        $factory = cash()->getEntityFactory(cashTransaction::class);
        $transaction = $factory->createNew();

        $paramsDto = new cashTransactionSaveParamsDto();
        if ($request->getTransferAccountId()) {
            $paramsDto->transfer = [
                'account_id' => $request->getTransferAccountId(),
                'incoming_amount' => $request->getTransferIncomingAmount(),
            ];
        }

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)
            ->findById($request->getCategoryId());
        if (!$category) {
            throw new kmwaNotFoundException(_w('Category not found'));
        }

        $account = cash()->getEntityRepository(cashAccount::class)
            ->findById($request->getAccountId());
        if (!$account) {
            throw new kmwaNotFoundException(_w('Account not found'));
        }

        $paramsDto->categoryType = $category->getType();

        if (!$request->getContractorContactId() && !empty($request->getContractor())) {
            $newContractor = new waContact();
            $newContractor->set('name', $request->getContractor());
            $newContractor->save();
            $request->setContractorContactId($newContractor->getId());
        }

        $data = [
            'amount' => $request->getAmount(),
            'date' => $request->getDate()->format('Y-m-d'),
            'account_id' => $request->getAccountId(),
            'category_id' => $request->getCategoryId(),
            'contractor_contact_id' => $request->getContractorContactId(),
            'contractor' => $request->getContractor(),
            'is_repeating' => $request->getIsRepeating(),
            'repeating_frequency' => $request->getRepeatingFrequency(),
            'repeating_interval' => $request->getRepeatingInterval(),
            'repeating_end_type' => $request->getRepeatingEndType(),
            'repeating_end_after' => $request->getRepeatingEndAfter(),
            'repeating_end_ondate' => $request->getRepeatingEndOndate() ? $request->getRepeatingEndOndate()->format('Y-m-d') : null,
            'transfer_account_id' => $request->getTransferAccountId(),
            'transfer_incoming_amount' => $request->getTransferIncomingAmount(),
            'is_onbadge' => $request->isOnbadge(),
            'description' => $request->getDescription(),
        ];

        if ($request->getExternal()) {
            $data['external_source'] = $request->getExternal()->getSource();
            $data['external_id'] = $request->getExternal()->getId();
            $data['external_data'] = $request->getExternal()->getData();
        }

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

        if ($request->getIsRepeating()) {
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
