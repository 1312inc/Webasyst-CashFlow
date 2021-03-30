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
        $repeatingDto->end_type = $request->repeating_end_type;
        $repeatingDto->interval = $request->repeating_interval;
        $repeatingDto->frequency = $request->repeating_frequency;
        $repeatingDto->apply_to_all_in_future = $request->apply_to_all_in_future;
        $repeatingDto->end = [
            'after' => $request->repeating_end_after,
            'ondate' => $request->repeating_end_ondate,
        ];

        /** @var cashTransaction $transaction */
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
        $newTransactionIds = [];

        if ($repeatingDto->apply_to_all_in_future) {
            $data['date'] = $transaction->getDate();
            $data['datetime'] = $transaction->getDatetime();

            $transaction = $this->populateTransaction($transaction, $data, $paramsDto);

            /** @var cashRepeatingTransaction $repeatingTransaction */
            $repeatingTransaction = $transaction->getRepeatingTransaction();
            kmwaAssert::instance($repeatingTransaction, cashRepeatingTransaction::class);

            $repeatingTransaction
                ->setAccountId($transaction->getAccountId())
                ->setCategoryId($transaction->getCategoryId())
                ->setDescription($transaction->getDescription())
                ->setAmount($transaction->getAmount())
                ->setContractorContactId($transaction->getContractorContactId())
                ->setIsOnbadge($transaction->getIsOnbadge());
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
                    ->setIsOnbadge($transaction->getIsOnbadge());

                $this->saver->addToPersist($t);
            }

            $saved = $this->saver->persistTransactions();
            foreach ($saved as $item) {
                if ($item instanceof cashRepeatingTransaction) {
                    continue;
                }

                $newTransactionIds[] = $item->getId();
            }
        } else {
            $transaction = $this->populateTransaction($transaction, $data, $paramsDto);

            $this->saver->addToPersist($transaction);
            $saved = $this->saver->persistTransactions();
            foreach ($saved as $item) {
                $newTransactionIds[] = $item->getId();
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
            throw new kmwaRuntimeException($this->saver->getError());
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not edit transaction'));
        }

        return $transaction;
    }
}
