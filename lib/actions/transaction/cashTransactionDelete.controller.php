<?php

/**
 * Class cashTransactionDeleteController
 */
class cashTransactionDeleteController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($this->getId());
        kmwaAssert::instance($transaction, cashTransaction::class);

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not delete this transaction'));
        }

        if (!cash()->getEntityPersister()->delete($transaction)) {
            $this->errors[] = _w('Error while deleting transaction');

            return;
        }

        $all = filter_var(waRequest::post('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($all) {
            /** @var cashRepeatingTransaction $repeatingTransaction */
            $repeatingTransaction = $transaction->getRepeatingTransaction();
            kmwaAssert::instance($repeatingTransaction, cashRepeatingTransaction::class);

            cash()->getModel(cashTransaction::class)->deleteAllRepeatingAfterTransaction(
                $repeatingTransaction->getId(),
                $transaction->getId()
            );

            if (!(new cashShopIntegration())->isShopForecastRepeatingTransaction($repeatingTransaction)) {
                $repeatingTransaction->setEnabled(0);
                cash()->getEntityPersister()->update($repeatingTransaction);
            }
        }
    }
}
