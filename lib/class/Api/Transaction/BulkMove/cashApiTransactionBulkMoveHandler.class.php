<?php

final class cashApiTransactionBulkMoveHandler implements cashApiHandlerInterface
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
     * @param cashApiTransactionBulkMoveRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     *
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaForbiddenException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws kmwaRuntimeException
     * @throws waDbException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->getIds());
        if (!$transactions) {
            return [];
        }

        $saver = new cashTransactionSaver();
        $updateData = [];

        $account = null;
        if ($request->getAccountId()) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById($request->getAccountId());
            kmwaAssert::instance($account, cashAccount::class);

            if (!cash()->getContactRights()->hasMinimumAccessToAccount(wa()->getUser(), $account->getId())) {
                throw new kmwaForbiddenException(_w('You have no access to this account'));
            }

            $updateData['account_id'] = $account->getId();
        }

        if ($request->getCategoryId()) {
            /** @var cashAccount $category */
            $category = cash()->getEntityRepository(cashCategory::class)->findById($request->getCategoryId());
            kmwaAssert::instance($category, cashCategory::class);

            if (!cash()->getContactRights()->hasMinimumAccessToCategory(wa()->getUser(), $category->getId())) {
                throw new kmwaForbiddenException(_w('You have no access to this category'));
            }

            $updateData['category_id'] = $category->getId();
        }

        if ($request->getContractorContactId()) {
            $contractorContact = new waContact($request->getContractorContactId());
            if ($contractorContact->exists()) {
                $updateData['contractor_contact_id'] = $contractorContact->getId();
            }
        } elseif ($request->getContractorName()) {
            $newContractor = new waContact();
            $newContractor->set('name', $request->getContractorName());
            $newContractor->save();
            $updateData['contractor_contact_id'] = $newContractor->getId();
        }

        $fields = cash()->getModel(cashTransaction::class)->getMetadata();
        $params = new cashTransactionSaveParamsDto();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to edit this transaction'));
            }

            $transactionData = cash()->getHydrator()->extract($transaction, [], $fields);
            $saveData = array_merge($transactionData, $updateData);

            if ($saver->populateFromArray($transaction, $saveData, $params)) {
                $saver->addToPersist($transaction);
            }

            if ($params->repeating) {
                $transferTransaction = $saver->createTransfer($transaction, $params);
                if ($transferTransaction) {
                    $saver->addToPersist($transferTransaction);
                }
            }
        }

        $saved = $saver->persistTransactions();
        $response = [];
        foreach ($saved as $savedT) {
            $dto = $this->transactionResponseDtoAssembler->generateResponseFromEntity($savedT);
            $dto->affected_transaction_ids = [$dto->id];
            $dto->affected_transactions = 1;
            $response[] = $dto;
        }

        return $response;
    }
}
