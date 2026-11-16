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

        $big_bang_date = null;
        if (cashHelper::isPremium()) {
            $big_bang_date = (new waAppSettingsModel())->get('cash', 'big_bang_date', null);
            if ($big_bang_date) {
                $big_bang_date = (new DateTimeImmutable($big_bang_date))->setTime(0, 0);
            }
        }

        $saver = new cashTransactionSaver();
        foreach ($transactions as $transaction) {
            if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
                throw new kmwaForbiddenException(_w('You are not allowed to delete this transaction'));
            }
            if ($big_bang_date) {
                $date = (new DateTimeImmutable($transaction->getDate()))->setTime(0, 0);
                if ($date < $big_bang_date) {
                    continue;
                }
            }

            $transaction->setIsArchived(1);
            $saver->addToPersist($transaction);
        }

        return array_map(function ($t) {return (int) $t->getId();}, $saver->persistTransactions());
    }
}
