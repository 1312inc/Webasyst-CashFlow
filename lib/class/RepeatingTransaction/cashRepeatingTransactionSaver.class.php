<?php

/**
 * Class cashRepeatingTransactionSaver
 */
class cashRepeatingTransactionSaver extends cashTransactionSaver
{
    /** @var cashRepeatingTransactionFactory */
    private $repeatingTransactionFactory;

    /**
     * cashRepeatingTransactionSaver constructor.
     */
    public function __construct()
    {
        $this->repeatingTransactionFactory = new cashRepeatingTransactionFactory();
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
                    $repeatingSettings->interval ?: cashRepeatingTransaction::INTERVAL_DAY
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
     * @return bool|cashRepeatingTransaction
     * @throws waException
     */
    public function saveFromTransaction(
        cashTransaction $transaction,
        cashRepeatingTransactionSettingsDto $repeatingSettings,
        $cloneSourceTransaction = false
    ) {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->startTransaction();
        try {
            $repeatingT = $this->repeatingTransactionFactory->createNew();
            $this->copyFromTransaction($repeatingT, $transaction);
            $this->applySettings($repeatingT, $repeatingSettings);

            cash()->getEntityPersister()->save($repeatingT);

            $model->commit();

            if (!$cloneSourceTransaction && !$transaction->getRepeatingId()) {
                $transaction->setRepeatingId($repeatingT->getId());
                cash()->getEntityPersister()->save($transaction);
            }

            return $repeatingT;
        } catch (Exception $ex) {
            $model->rollback();

            $this->error = $ex->getMessage();
            cash()->getLogger()->error('Repeating transaction save error', $ex);
        }

        return false;
    }

    /**
     * @param cashRepeatingTransaction            $repeatingT
     * @param cashTransaction                     $transaction
     * @param cashRepeatingTransactionSettingsDto $repeatingSettings
     *
     * @return bool|cashTransaction
     * @throws waException
     */
    public function saveExisting(
        cashRepeatingTransaction $repeatingT,
        cashTransaction $transaction,
        cashRepeatingTransactionSettingsDto $repeatingSettings
    ) {
        $hydrator = cash()->getHydrator();
        $persister = cash()->getEntityPersister();

        $newRepeatingT = $this->repeatingTransactionFactory->createNew();
        $this->copyFromTransaction($newRepeatingT, $transaction);
        $this->applySettings($newRepeatingT, $repeatingSettings);

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();
        try {
            $repeatingT->setEnabled(0);
            $persister->save($newRepeatingT);
            $persister->save($repeatingT);

            if ($this->repeatingSettingsChanged($repeatingT, $newRepeatingT)) {
                $model->deleteAllByRepeatingIdAfterDate($repeatingT->getId(), $transaction->getDate());
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
                    parent::saveFromArray($t, $data, $params);
                }
            }

            $model->commit();

            return $newRepeatingT;
        } catch (Exception $ex) {
            $model->rollback();
        }

        return false;
    }

    /**
     * @param cashRepeatingTransaction $repeatingT
     * @param cashTransaction          $transaction
     */
    private function copyFromTransaction(cashRepeatingTransaction $repeatingT, cashTransaction $transaction)
    {
        $repeatingT
            ->setAccountId($transaction->getAccountId())
            ->setCategoryId($transaction->getCategoryId())
            ->setCreateContactId($transaction->getCreateContactId())
            ->setAmount($transaction->getAmount())
            ->setDescription($transaction->getDescription())
            ->setDate($transaction->getDate())
            ->setExternalHash($transaction->getExternalHash())
            ->setExternalSource($transaction->getExternalSource())
        ;
    }

    /**
     * @param cashRepeatingTransaction            $repeatingT
     * @param cashRepeatingTransactionSettingsDto $repeatingSettings
     */
    private function applySettings(
        cashRepeatingTransaction $repeatingT,
        cashRepeatingTransactionSettingsDto $repeatingSettings
    ) {
        if (!empty($repeatingSettings->frequency)) {
            $repeatingT->setRepeatingFrequency($repeatingSettings->frequency);
        } elseif (empty($repeatingT->getRepeatingFrequency())) {
            $repeatingT->setRepeatingFrequency(cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY);
        }

        if ($repeatingSettings->interval) {
            $repeatingT->setRepeatingInterval($repeatingSettings->interval);
        } elseif (empty($repeatingT->getRepeatingInterval())) {
            $repeatingT->setRepeatingInterval(cashRepeatingTransaction::INTERVAL_DAY);
        }

        if ($repeatingSettings->end_type) {
            $repeatingT->setRepeatingEndType($repeatingSettings->end_type);
        } elseif (empty($repeatingT->getRepeatingEndType())) {
            $repeatingT->setRepeatingEndType(cashRepeatingTransaction::REPEATING_END_NEVER);
        }

        if ($repeatingSettings->end_type) {
            $repeatingT->setRepeatingEndConditions($repeatingSettings->end);
        }

        if ($repeatingSettings->transfer) {
            $repeatingT->setTransfer($repeatingSettings->transfer);
        }
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
    ) {
        return $transaction->getRepeatingEndConditions() != $transaction2->getRepeatingEndConditions()
            || $transaction->getRepeatingFrequency() != $transaction2->getRepeatingFrequency()
            || $transaction->getRepeatingInterval() != $transaction2->getRepeatingInterval()
            || $transaction->getRepeatingEndType() != $transaction2->getRepeatingEndType();
    }
}
