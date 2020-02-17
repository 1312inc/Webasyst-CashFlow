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
     * @param cashRepeatingTransaction $transaction
     * @param array                    $data
     * @param array                    $params
     *
     * @return bool|cashRepeatingTransaction
     * @throws waException
     */
    public function saveFromArray($transaction, array $data, array $params = [])
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
            $repeatingT = $this->repeatingTransactionFactory->createNew();
            $this->fill($repeatingT, $transaction, $repeatingSettings);
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

    /**
     * @param cashRepeatingTransaction $repeatingT
     * @param cashTransaction          $transaction
     * @param array                    $repeatingSettings
     *
     * @return bool|cashTransaction
     * @throws waException
     */
    public function saveExisting(cashRepeatingTransaction $repeatingT, cashTransaction $transaction, array $repeatingSettings)
    {
        $hydrator = cash()->getHydrator();
        $persister = cash()->getEntityPersister();

        /** @var cashRepeatingTransaction $newRepeatingT */
        $newRepeatingT = $this->repeatingTransactionFactory->createNew();
        $this->fill($newRepeatingT, $transaction, $repeatingSettings);

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();
        try {
            if ($this->repeatingSettingsChanged($repeatingT, $newRepeatingT)) {
                $persister->save($newRepeatingT);
                $model->deleteAllByRepeatingIdAfterDate($repeatingT->getId(), $transaction->getDate());
                $repeatingT->setEnabled(0);
                $return = $newRepeatingT;
            } else {
                $transactions = cash()->getEntityRepository(cashTransaction::class)->findAllByRepeatingIdAndAfterDate(
                    $repeatingT->getId(),
                    $transaction->getDate()
                );
                $data = $hydrator->extract($transaction);
                unset($data['id'], $data['date'], $data['datetime'], $data['create_datetime']);
                foreach ($transactions as $t) {
                    parent::saveFromArray($t, $data);
                }
                $return = $repeatingT;
            }
            $persister->save($repeatingT);

            $model->commit();

            return $return;
        } catch (Exception $ex) {
            $model->rollback();
        }

        return false;
    }

    /**
     * @param cashRepeatingTransaction $repeatingT
     * @param cashTransaction          $transaction
     * @param array                    $repeatingSettings
     */
    private function fill(cashRepeatingTransaction $repeatingT, cashTransaction $transaction, array $repeatingSettings)
    {
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
    }

    /**
     * @param cashRepeatingTransaction $transaction
     * @param cashRepeatingTransaction $transaction2
     *
     * @return bool
     */
    private function repeatingSettingsChanged(cashRepeatingTransaction $transaction, cashRepeatingTransaction $transaction2)
    {
        return $transaction->getRepeatingEndConditions() != $transaction2->getRepeatingEndConditions()
            || $transaction->getRepeatingFrequency() != $transaction2->getRepeatingFrequency()
            || $transaction->getRepeatingInterval() != $transaction2->getRepeatingInterval();
    }
}
