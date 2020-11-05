<?php

/**
 * Class cashApiTransactionBulkDeleteHandler
 */
class cashApiTransactionBulkDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionBulkDeleteRequest $request
     *
     * @return bool
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->ids);
        if (!$transactions) {
            return true;
        }

        $persister = cash()->getEntityPersister();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to delete this transaction'));
            }

            $transaction->setIsArchived(1);
            $persister->update($transaction);
        }

        return true;
    }
}
