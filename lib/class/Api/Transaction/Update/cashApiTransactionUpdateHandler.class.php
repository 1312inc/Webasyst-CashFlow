<?php

/**
 * Class cashApiTransactionUpdateHandler
 */
class cashApiTransactionUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @var cashTransactionSaver
     */
    private $saver;

    /**
     * @var cashApiTransactionResponseDtoAssembler
     */
    private $transactionResponseDtoAssembler;

    /**
     * cashApiTransactionUpdateHandler constructor.
     */
    public function __construct()
    {
        $this->saver = new cashTransactionSaver();
        $this->transactionResponseDtoAssembler = new cashApiTransactionResponseDtoAssembler();
    }

    /**
     * @param cashApiTransactionUpdateRequest $request
     *
     * @return array|cashApiTransactionResponseDto
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
        $repeatingDto->apply_to_all_in_future = $request->isApplyToAllInFuture();

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($request->getId());
        if (!$transaction) {
            throw new kmwaNotFoundException(_w('No transaction'));
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not edit transaction'));
        }

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
            'is_self_destruct_when_due' => $request->isSelfDestructWhenDue(),
        ];

        if ($request->getExternal()) {
            $data['external_source'] = $request->getExternal()->getSource();
            $data['external_id'] = $request->getExternal()->getId();
            $data['external_data'] = $request->getExternal()->getData();
        }

        $newTransactionIds = [];

        if ($repeatingDto->apply_to_all_in_future) {
            $data['date'] = $transaction->getDate();
            $data['datetime'] = $transaction->getDatetime();

            $transaction = $this->populateTransaction($transaction, $data, $paramsDto);

            /** @var cashRepeatingTransaction $repeatingTransaction */
            $repeatingTransaction = $transaction->getRepeatingTransaction();
            if ($repeatingTransaction) {
                kmwaAssert::instance($repeatingTransaction, cashRepeatingTransaction::class);

                $repeatingTransaction
                    ->setAccountId($transaction->getAccountId())
                    ->setCategoryId($transaction->getCategoryId())
                    ->setDescription($transaction->getDescription())
                    ->setAmount($transaction->getAmount())
                    ->setContractorContactId($transaction->getContractorContactId())
                    ->setIsOnbadge($transaction->getIsOnbadge())
                    ->setIsSelfDestructWhenDue($transaction->getIsSelfDestructWhenDue());
                $this->saver->addToPersist($repeatingTransaction);

                $transactions = cash()->getEntityRepository(cashTransaction::class)->findAllByRepeatingIdAndAfterDate(
                    $repeatingTransaction->getId(),
                    $transaction->getDate()
                );
                foreach ($transactions as $t) {
                    $t->setAccountId($transaction->getAccountId())
                        ->setCategoryId($transaction->getCategoryId())
                        ->setDescription($transaction->getDescription())
                        ->setAmount($transaction->getAmount())
                        ->setContractorContactId($transaction->getContractorContactId())
                        ->setIsOnbadge($transaction->getIsOnbadge())
                        ->setIsSelfDestructWhenDue($transaction->getIsSelfDestructWhenDue());

                    $this->saver->addToPersist($t);
                }

                $saved = $this->saver->persistTransactions();
                foreach ($saved as $item) {
                    if ($item instanceof cashRepeatingTransaction) {
                        continue;
                    }
                    $newTransactionIds[] = (int) $item->getId();
                }
            }
        } else {
            $transaction = $this->populateTransaction($transaction, $data, $paramsDto);

            $this->saver->addToPersist($transaction);
            $saved = $this->saver->persistTransactions();
            foreach ($saved as $item) {
                $newTransactionIds[] = (int) $item->getId();
            }
        }

        $transactionModel = cash()->getModel(cashTransaction::class);
        $data = $transactionModel->getById($transaction->getId());
        $response = $this->transactionResponseDtoAssembler->fromData($data);
        $response->affected_transaction_ids = $newTransactionIds;
        $response->affected_transactions = count($newTransactionIds);

        return $response;
    }

    /**
     * @param cashTransaction              $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $paramsDto
     *
     * @return cashTransaction
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function populateTransaction(
        cashTransaction $transaction,
        array $data,
        cashTransactionSaveParamsDto $paramsDto
    ): cashTransaction {
        if (!$this->saver->populateFromArray($transaction, $data, $paramsDto)) {
            throw new kmwaRuntimeException($this->saver->getError(), 404);
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not edit transaction'), 403);
        }

        return $transaction;
    }
}
