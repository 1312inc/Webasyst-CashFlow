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

        if (!$saver->saveFromArray($transaction, $data, ['transfer' => $transfer])) {
            $this->errors[] = $saver->getError();

            return;
        }

        if ($repeating) {
            $repeatTransactionSaver = new cashRepeatingTransactionSaver();
            $transactionRepeater = new cashTransactionRepeater();
            if ($isNew) {
                $repeatingTransaction = $repeatTransactionSaver->saveFromTransaction(
                    $transaction,
                    $repeating
                );
                $transactionRepeater->repeat($repeatingTransaction);
            } elseif (!empty($repeating['apply_to_all_in_future'])) {
                $repeatingTransaction = $transaction->getRepeatingTransaction();
                $savedRepeatingTransaction = $repeatTransactionSaver->saveExisting($repeatingTransaction, $transaction, $repeating);
                if (!$repeatingTransaction instanceof cashRepeatingTransaction) {
                    throw new kmwaRuntimeException('Error on repeating transaction save');
                }
                // изменились настройки повторения и вернулся новый объект повторяющейся транзакции
                if ($savedRepeatingTransaction->getId() !== $repeatingTransaction->getId()) {
                    $transactionRepeater->repeat($savedRepeatingTransaction);
                }
            }

        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $this->response = $transactionDto;
    }
}
