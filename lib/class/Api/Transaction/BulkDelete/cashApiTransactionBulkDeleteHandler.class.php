<?php

/**
 * Class cashApiTransactionBulkDeleteHandler
 */
class cashApiTransactionBulkDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionBulkDeleteRequest $request
     *
     * @param $request
     * @return array
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->ids);
        if (!$transactions) {
            return [];
        }

        $saver = new cashTransactionSaver();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to delete this transaction'));
            }

            $transaction->setIsArchived(1);
            $saver->addToPersist($transaction);
        }

        return array_map(function ($t) {return (int) $t->getId();}, $saver->persistTransactions());
    }
}
