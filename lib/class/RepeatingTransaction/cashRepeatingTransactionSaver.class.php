<?php

/**
 * Class cashRepeatingTransactionSaver
 */
class cashRepeatingTransactionSaver extends cashTransactionSaver
{
    /**
     * @var cashRepeatingTransactionFactory
     */
    private $repeatingTransactionFactory;

    /**
     * cashRepeatingTransactionSaver constructor.
     */
    public function __construct()
    {
        $this->repeatingTransactionFactory = cash()->getEntityFactory(cashRepeatingTransaction::class);
    }

    /**
     * @param cashRepeatingTransaction     $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashRepeatingTransaction
     * @throws waException
     */
    public function saveFromArray($transaction, array $data, cashTransactionSaveParamsDto $params)
    {
        if (!$this->validate($data)) {
            return false;
        }

        $repeatingSettings = new cashRepeatingTransactionSettingsDto($params->repeating);

        /** @var cashRepeatingTransaction $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->startTransaction();
        try {
            $data = $this->addCategoryId($data);
            cash()->getHydrator()->hydrate($transaction, $data);

            $transaction
                ->setRepeatingFrequency(
                    $repeatingSettings->frequency ?: cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY
                )
                ->setRepeatingInterval(
                    $repeatingSettings->interval ?: cashRepeatingTransaction::INTERVAL_MONTH
                )
                ->setRepeatingEndType(
                    $repeatingSettings->end_type ?: cashRepeatingTransaction::REPEATING_END_NEVER
                )
                ->setRepeatingEndConditions($repeatingSettings->end);

            cash()->getEntityPersister()->save($transaction);

            $model->commit();

            return $transaction;
        } catch (Exception $ex) {
            $model->rollback();

            $this->error = $ex->getMessage();
        }

        return false;
    }

    /**
     * @param cashTransaction                     $transaction
     * @param cashRepeatingTransactionSettingsDto $repeatingSettings
     * @param bool                                $cloneSourceTransaction
     *
     * @return cashRepeatingTransactionSaveResultDto
     * @throws waException
     */
    public function saveFromTransaction(
        cashTransaction $transaction,
        cashRepeatingTransactionSettingsDto $repeatingSettings,
        $cloneSourceTransaction = false
    ): cashRepeatingTransactionSaveResultDto {
        $result = new cashRepeatingTransactionSaveResultDto();

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->startTransaction();
        try {
            $repeatingT = $this->repeatingTransactionFactory->createFromTransactionWithRepeatingSettings(
                $transaction,
                $repeatingSettings
            );

            cash()->getEntityPersister()->save($repeatingT);
            $result->newTransaction = $repeatingT;
//
//            if (!$cloneSourceTransaction && !$transaction->getRepeatingId()) {
//                $transaction->setRepeatingId($repeatingT->getId());
//            }

            $model->commit();

            $result->ok = true;
            $result->shouldRepeat = true;
        } catch (Exception $ex) {
            $model->rollback();

            $result->error = $ex->getMessage();
            cash()->getLogger()->error('Repeating transaction save error', $ex);
        }

        return $result;
    }

    /**
     * @param cashRepeatingTransaction            $repeatingT
     * @param cashTransaction                     $transaction
     * @param cashRepeatingTransactionSettingsDto $repeatingSettings
     *
     * @return cashRepeatingTransactionSaveResultDto
     * @throws waException
     */
    public function saveExisting(
        cashRepeatingTransaction $repeatingT,
        cashTransaction $transaction,
        cashRepeatingTransactionSettingsDto $repeatingSettings
    ): cashRepeatingTransactionSaveResultDto {
        $hydrator = cash()->getHydrator();
        $persister = cash()->getEntityPersister();

        $newRepeatingT = $this->repeatingTransactionFactory->createFromTransactionWithRepeatingSettings(
            $transaction,
            $repeatingSettings
        );

        $result = new cashRepeatingTransactionSaveResultDto();
        $result->oldTransaction = $repeatingT;

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();
        try {
            $repeatingT->setEnabled(0);
            $persister->save($newRepeatingT);
            $persister->save($repeatingT);
            $result->newTransaction = $newRepeatingT;

            if ($this->repeatingSettingsChanged($repeatingT, $newRepeatingT)) {
                $model->deleteAllByRepeatingIdAfterDate($repeatingT->getId(), $transaction->getDate());
                $result->shouldRepeat = true;
            } else {
                $transactions = cash()->getEntityRepository(cashTransaction::class)->findAllByRepeatingIdAndAfterDate(
                    $repeatingT->getId(),
                    $transaction->getDate()
                );
                $transaction->setRepeatingTransaction($newRepeatingT);
                $data = $hydrator->extract($transaction, array_keys($model->getMetadata()));
                unset($data['id'], $data['date'], $data['datetime'], $data['create_datetime']);

                $params = new cashTransactionSaveParamsDto();
                foreach ($transactions as $t) {
                    $this->addToPersist(parent::populateFromArray($t, $data, $params));
                }
                $this->persistTransactions();
            }

            $model->commit();

            $result->ok = true;
        } catch (Exception $ex) {
            $model->rollback();

            $result->error = $ex->getMessage();
            cash()->getLogger()->error('Repeating transaction save error', $ex);
        }

        return $result;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param cashRepeatingTransaction $transaction2
     *
     * @return bool
     */
    private function repeatingSettingsChanged(
        cashRepeatingTransaction $transaction,
        cashRepeatingTransaction $transaction2
    ): bool {
        return $transaction->getRepeatingEndConditions() != $transaction2->getRepeatingEndConditions()
            || $transaction->getRepeatingFrequency() != $transaction2->getRepeatingFrequency()
            || $transaction->getRepeatingInterval() != $transaction2->getRepeatingInterval()
            || $transaction->getRepeatingEndType() != $transaction2->getRepeatingEndType()
            || $transaction->getDate() != $transaction2->getDate();
    }
}
