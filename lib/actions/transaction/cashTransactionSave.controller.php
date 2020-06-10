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

        $repeatingDto = new cashRepeatingTransactionSettingsDto($repeating);
        $repeatTransactionSaver = new cashRepeatingTransactionSaver();
        $transactionRepeater = new cashTransactionRepeater();

        $paramsDto = new cashTransactionSaveParamsDto();
        $paramsDto->transfer = $transfer;
        $paramsDto->categoryType = $categoryType;

        switch (true) {
            case $isNew && $isRepeating && $repeating && $repeatingDto->interval: // новая повторяющаяся
                /** @var cashRepeatingTransaction $repeatingTransaction */
                $repeatingTransaction = cash()->getEntityFactory(cashRepeatingTransaction::class)->createNew();
                if (!$repeatTransactionSaver->saveFromArray($repeatingTransaction, $data,$paramsDto)) {
                    $this->errors[] = 'Error on new repeating transaction save';

                    return;
                }

                $transactionRepeater->repeat($repeatingTransaction);

                if ($transaction->getLinkedTransaction()) {
                    $repeatingTransaction = $repeatTransactionSaver->saveFromTransaction(
                        $transaction->getLinkedTransaction(),
                        $repeatingDto
                    );
                    $transactionRepeater->repeat($repeatingTransaction);
                }
                break;

            case $isRepeating && $repeating && $repeatingDto->apply_to_all_in_future: // обновление повторяющийся
                $repeatingTransaction = $transaction->getRepeatingTransaction();
                $savedRepeatingTransaction = $repeatTransactionSaver->saveExisting(
                    $repeatingTransaction,
                    $transaction,
                    $repeatingDto
                );

                if (!$savedRepeatingTransaction instanceof cashRepeatingTransaction) {
                    throw new kmwaRuntimeException('Error on repeating transaction save');
                }

                $transactionRepeater->repeat($savedRepeatingTransaction);

            default: // обычная транзакция (создание и обновление)
                if (!$saver->saveFromArray($transaction, $data, $paramsDto)) {
                    $this->errors[] = $saver->getError();

                    return;
                }
        }



        if (() || $repeatingDto->apply_to_all_in_future || $repeatingDto->interval) {
            if ($isNew) {

            } elseif ($repeatingDto->apply_to_all_in_future) {


                // изменились настройки повторения и вернулся новый объект повторяющейся транзакции
//                if ($savedRepeatingTransaction->getId() !== $repeatingTransaction->getId()) {
//                    $transactionRepeater->repeat($savedRepeatingTransaction);
//                }
            }
        }

        $transactionDto = (new cashTransactionDtoAssembler())->createFromEntity($transaction);
        $this->response = $transactionDto;
    }
}
