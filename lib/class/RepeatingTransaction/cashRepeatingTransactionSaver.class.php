<?php

/**
 * Class cashRepeatingTransactionSaver
 */
class cashRepeatingTransactionSaver extends cashTransactionSaver
{
    /**
     * @param array $data
     * @param array $params
     *
     * @return bool|cashRepeatingTransaction
     * @throws waException
     */
    public function saveFromArray(array $data, array $params = [])
    {
        if (!$this->validate($data)) {
            return false;
        }

        $repeatingSettings = $params['repeating'];

        /** @var cashRepeatingTransaction $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->startTransaction();

        try {
            /** @var cashRepeatingTransaction $transaction */
            if (!empty($data['id'])) {
                $transaction = cash()->getEntityRepository(cashRepeatingTransaction::class)->findById($data['id']);
                kmwaAssert::instance($transaction, cashRepeatingTransaction::class);
                unset($data['id']);
            } else {
                $transaction = cash()->getEntityFactory(cashRepeatingTransaction::class)->createNew();
            }

            $data = $this->addCategoryId($data);

            cash()->getHydrator()->hydrate($transaction, $data);

            $transaction
                ->setRepeatingFrequency(
                    !empty($repeatingSettings['frequency'])
                        ? $repeatingSettings['frequency']
                        : cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY
                )
                ->setRepeatingInterval(
                    !empty($repeatingSettings['interval'])
                        ? $repeatingSettings['interval']
                        : cashRepeatingTransaction::INTERVAL_DAY
                )
                ->setRepeatingEndConditions($repeatingSettings['end']);

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
     * @param cashTransaction $transaction
     * @param array           $repeatingSettings
     * @param bool            $cloneSourceTransaction
     *
     * @return bool|cashRepeatingTransaction
     * @throws waException
     */
    public function saveFromTransaction(
        cashTransaction $transaction,
        array $repeatingSettings,
        $cloneSourceTransaction = false
    ) {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->startTransaction();

        try {
            /** @var cashRepeatingTransaction $repeatingT */
            $repeatingT = cash()->getEntityFactory(cashRepeatingTransaction::class)->createNew();

            $repeatingT
                ->setAccountId($transaction->getAccountId())
                ->setCategoryId($transaction->getCategoryId())
                ->setCreateContactId($transaction->getCreateContactId())
                ->setAmount($transaction->getAmount())
                ->setDescription($transaction->getDescription())
                ->setDate($transaction->getDate())
                ->setRepeatingFrequency(
                    !empty($repeatingSettings['frequency'])
                        ? $repeatingSettings['frequency']
                        : cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY
                )
                ->setRepeatingInterval(
                    !empty($repeatingSettings['interval'])
                        ? $repeatingSettings['interval']
                        : cashRepeatingTransaction::INTERVAL_DAY
                )
                ->setRepeatingEndConditions($repeatingSettings['end']);

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
        }

        return false;
    }
}
