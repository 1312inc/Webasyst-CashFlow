<?php

/**
 * Class cashTransactionSaveController
 */
class cashTransactionSaveController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $data = waRequest::post('transaction', [], waRequest::TYPE_ARRAY);
        $transfer = waRequest::post('transfer', [], waRequest::TYPE_ARRAY);
        $repeating = waRequest::post('repeating', [], waRequest::TYPE_ARRAY);
        $isRepeating = waRequest::post('is_repeating', 0, waRequest::TYPE_INT);
        $categoryType = waRequest::post('category_type', cashCategory::TYPE_INCOME, waRequest::TYPE_STRING_TRIM);
        $contractor = waRequest::post('contractor', '', waRequest::TYPE_STRING_TRIM);

        $isNew = empty($data['id']);
        $newTransactionIds = [];

        $saver = new cashTransactionSaver();
        $repeatingDto = new cashRepeatingTransactionSettingsDto($repeating);
        $isRepeating = $isRepeating || $repeatingDto->apply_to_all_in_future;

        /** @var cashTransaction $transaction */
        if (!empty($data['id'])) {
            $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($data['id']);
            kmwaAssert::instance($transaction, cashTransaction::class);
            unset($data['id']);
        } else {
            $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
        }

        $paramsDto = new cashTransactionSaveParamsDto();
        $paramsDto->transfer = $transfer;
        $paramsDto->categoryType = $categoryType;

        if (empty($data['contractor_contact_id']) && !empty($contractor)) {
            $newContractor = new waContact();
            $newContractor->set('name', $contractor);
            $newContractor->save();
            $data['contractor_contact_id'] = $newContractor->getId();
        }

        // не обновлять дату если применяется ко всем следующим повторяющимся транзакциям
        if ($repeatingDto->apply_to_all_in_future) {
            $data['date'] = $transaction->getDate();
        }

        if (!$saver->populateFromArray($transaction, $data, $paramsDto)) {
            $this->errors[] = $saver->getError();

            return;
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You are not allowed to edit or create new transactions'));
        }

        if ($paramsDto->transfer) {
            $transferTransaction = $saver->createTransfer($transaction, $paramsDto);
        }

        // @todo: только ад и боль ждут дальше
        if ($isRepeating) {
            $repeatTransactionSaver = new cashRepeatingTransactionSaver();
            $transactionRepeater = new cashTransactionRepeater();

            if ($isNew) {
                $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                    $transaction,
                    $repeatingDto
                );

                if ($repeatingSaveResult->ok) {
                    $newTransactions = $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                    if ($newTransactions) {
                        foreach ($newTransactions as $newTransaction) {
                            $newTransactionIds[$newTransaction->getId()] = true;
                        }
                        wa()->getStorage()->set('cash_just_saved_transactions', $newTransactionIds);
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
                            $newTransactionIds[$newTransaction->getId()] = true;
                        }
                        wa()->getStorage()->set('cash_just_saved_transactions', $newTransactionIds);
                    }
                }

                return;
            }

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
                $t
                    ->setAccountId($transaction->getAccountId())
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
            wa()->getStorage()->set('cash_just_saved_transactions', $newTransactionIds);

            return;
        }

        $saver->addToPersist($transaction);
        if (isset($transferTransaction)) {
            $saver->addToPersist($transferTransaction);
        }

        $saved = $saver->persistTransactions();
        foreach ($saved as $item) {
            $newTransactionIds[$item->getId()] = true;
        }

        wa()->getStorage()->set('cash_just_saved_transactions', $newTransactionIds);
//        waLog::dump($newTransactionIds, 'krll.log');
    }
}
