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
        if (!$saver->saveFromArray($transaction, $data, $paramsDto)) {
            $this->errors[] = $saver->getError();

            return;
        }

        if ($isRepeating && $repeating) {
            $repeatingDto = new cashRepeatingTransactionSettingsDto($repeating);
            if($repeatingDto->interval) {
                $repeatTransactionSaver = new cashRepeatingTransactionSaver();
                $transactionRepeater = new cashTransactionRepeater();
                if ($isNew) {
                    $repeatingTransaction = $repeatTransactionSaver->saveFromTransaction(
                        $transaction,
                        $repeatingDto
                    );
                    $transactionRepeater->repeat($repeatingTransaction);
                } elseif (!empty($repeating['apply_to_all_in_future'])) {
                    $repeatingTransaction = $transaction->getRepeatingTransaction();
                    $savedRepeatingTransaction = $repeatTransactionSaver->saveExisting(
                        $repeatingTransaction,
                        $transaction,
                        $repeatingDto
                    );
                    if (!$repeatingTransaction instanceof cashRepeatingTransaction) {
                        throw new kmwaRuntimeException('Error on repeating transaction save');
                    }
                    // изменились настройки повторения и вернулся новый объект повторяющейся транзакции
//                if ($savedRepeatingTransaction->getId() !== $repeatingTransaction->getId()) {
//                    $transactionRepeater->repeat($savedRepeatingTransaction);
//                }
                }
            }
        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $this->response = $transactionDto;
    }
}
