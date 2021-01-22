<?php

/**
 * Class cashRepeatingTransactionFactory
 */
class cashRepeatingTransactionFactory extends cashBaseFactory
{
    /**
     * @return cashRepeatingTransaction
     */
    public function createNew()
    {
        return (new cashRepeatingTransaction())->setCreateContactId(wa()->getUser()->getId());
    }

    /**
     * @param cashTransaction $transaction
     *
     * @return cashRepeatingTransaction
     */
    public function createFromTransaction(cashTransaction $transaction): cashRepeatingTransaction
    {
        $tx = $this->createNew();

        $tx
            ->setAccountId($transaction->getAccountId())
            ->setCategoryId($transaction->getCategoryId())
            ->setCreateContactId($transaction->getCreateContactId())
            ->setContractorContactId($transaction->getContractorContactId())
            ->setAmount($transaction->getAmount())
            ->setDescription($transaction->getDescription())
            ->setDate($transaction->getDate())
            ->setExternalHash($transaction->getExternalHash())
            ->setExternalSource($transaction->getExternalSource())
            ->setIsOnbadge($transaction->getIsOnbadge());

        return $tx;
    }

    /**
     * @param cashTransaction                     $transaction
     * @param cashRepeatingTransactionSettingsDto $repeatingSettings
     *
     * @return cashRepeatingTransaction
     */
    public function createFromTransactionWithRepeatingSettings(
        cashTransaction $transaction,
        cashRepeatingTransactionSettingsDto $repeatingSettings
    ): cashRepeatingTransaction {
        $tx = $this->createFromTransaction($transaction);

        if (!empty($repeatingSettings->frequency)) {
            $tx->setRepeatingFrequency($repeatingSettings->frequency);
        } elseif (empty($tx->getRepeatingFrequency())) {
            $tx->setRepeatingFrequency(cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY);
        }

        if ($repeatingSettings->interval) {
            $tx->setRepeatingInterval($repeatingSettings->interval);
        } elseif (empty($tx->getRepeatingInterval())) {
            $tx->setRepeatingInterval(cashRepeatingTransaction::INTERVAL_DAY);
        }

        if ($repeatingSettings->end_type) {
            $tx->setRepeatingEndType($repeatingSettings->end_type);
        } elseif (empty($tx->getRepeatingEndType())) {
            $tx->setRepeatingEndType(cashRepeatingTransaction::REPEATING_END_NEVER);
        }

        if ($repeatingSettings->end_type) {
            $tx->setRepeatingEndConditions($repeatingSettings->end);
        }

        return $tx;
    }
}
