<?php

final class cashApiTransactionBulkCompleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionBulkCompleteRequest $request
     *
     * @return null|bool
     * @throws waException|kmwaForbiddenException
     */
    public function handle($request)
    {
        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->getIds());
        if (!$transactions) {
            return true;
        }

        $saver = new cashTransactionSaver();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to edit this transaction'));
            }

            $transaction->setIsOnbadge(false)
                ->setDate(date('Y-m-d'));
            $saver->addToPersist($transaction);
        }

        return $saver->persistTransactions();
    }
}
