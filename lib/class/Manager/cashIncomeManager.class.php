<?php

/**
 * Class cashIncomeManager
 */
class cashIncomeManager
{
    /**
     * @param cashTransaction   $transaction
     * @param cashAccount       $account
     * @param cashCategory|null $category
     *
     * @return bool|cashTransaction
     * @throws waException
     */
    public function add(cashTransaction $transaction, cashAccount $account, cashCategory $category = null)
    {
        $transaction
            ->setAccount($account)
            ->setCategory($category)
            ->setDate(date('Y-m-d'))
            ->setDatetime(date('Y-m-d H:i:s'));

        if (cash()->getEntityPersister()->insert($transaction)) {
            return $transaction;
        }

        return false;
    }
}
