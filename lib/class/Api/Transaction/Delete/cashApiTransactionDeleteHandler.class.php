<?php

/**
 * Class cashApiTransactionDeleteHandler
 */
class cashApiTransactionDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionDeleteRequest $request
     *
     * @return bool
     * @throws waException
     * @throws kmwaNotFoundException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     */
    public function handle($request)
    {
        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($request->id);
        if (!$transaction) {
            throw new kmwaNotFoundException(_w('No transaction'));
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can not delete this transaction'));
        }

        if (!cash()->getEntityPersister()->delete($transaction)) {
            throw new kmwaRuntimeException(_w('Error while deleting transaction'));
        }

        if ($request->all_repeating) {
            /** @var cashRepeatingTransaction $repeatingTransaction */
            $repeatingTransaction = $transaction->getRepeatingTransaction();

            if (!$repeatingTransaction) {
                throw new kmwaNotFoundException(_w('No repeating transaction'));
            }

            cash()->getModel(cashTransaction::class)->deleteAllRepeatingAfterTransaction(
                $repeatingTransaction->getId(),
                $transaction->getId()
            );

            if (!(new cashShopIntegration())->isShopForecastRepeatingTransaction($repeatingTransaction)) {
                $repeatingTransaction->setEnabled(0);
                cash()->getEntityPersister()->update($repeatingTransaction);
            }
        }

        return true;
    }
}
