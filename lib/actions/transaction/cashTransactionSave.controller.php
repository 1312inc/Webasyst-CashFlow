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

        $isNew = empty($data['id']);

        $saver = new cashTransactionSaver();

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
        if (!$saver->populateFromArray($transaction, $data, $paramsDto)) {
            $this->errors[] = $saver->getError();

            return;
        }

        $repeatingDto = new cashRepeatingTransactionSettingsDto($repeating);
        if ($repeatingDto->isLoaded()) {
            $repeatTransactionSaver = new cashRepeatingTransactionSaver();
            $transactionRepeater = new cashTransactionRepeater();

            if ($isNew) {
                $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                    $transaction,
                    $repeatingDto
                );

                // первая транзакция уже создана
//                $endAfter = $repeatingTransaction->getRepeatingConditionEndAfter();
//                if ($endAfter) {
//                    $repeatingTransaction->setRepeatingConditionEndAfter(--$endAfter);
//                }
                if ($repeatingSaveResult->ok) {
                    $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                }

                if ($transaction->getLinkedTransaction()) {
                    $repeatingSaveResult = $repeatTransactionSaver->saveFromTransaction(
                        $transaction->getLinkedTransaction(),
                        $repeatingDto
                    );

                    // первая транзакция уже создана
//                    $repeatingTransaction->setRepeatingConditionEndAfter($endAfter);
                    $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                }
            } elseif ($repeatingDto->apply_to_all_in_future) {
                $repeatingTransaction = $transaction->getRepeatingTransaction();
                kmwaAssert::instance($repeatingTransaction, cashRepeatingTransaction::class);

                $repeatingSaveResult = $repeatTransactionSaver->saveExisting(
                    $repeatingTransaction,
                    $transaction,
                    $repeatingDto
                );

                if (!$repeatingSaveResult->ok) {
                    throw new kmwaRuntimeException('Error on repeating transaction save');
                }

                // изменились настройки повторения и вернулся новый объект повторяющейся транзакции
                if ($repeatingSaveResult->shouldRepeat) {
                    $transactionRepeater->repeat($repeatingSaveResult->newTransaction);
                }
            }
        } else {
            $saver->addToPersist($transaction);
            if ($paramsDto->transfer) {
                $transferTransaction = $saver->createTransfer($transaction, $paramsDto);
                if ($transferTransaction) {
                    $saver->addToPersist($transferTransaction);
                }
            }
            $saver->persistTransactions();
        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $this->response = $transactionDto;
    }
}
