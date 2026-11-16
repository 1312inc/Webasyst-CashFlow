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
                throw new kmwaForbiddenException(sprintf_wp(
                    'You are not allowed to edit this transaction (ID %s, %s)',
                    $transaction->getId(),
                    $transaction->getDescription()
                ));
            }

            if ($big_bang_date) {
                $date = (new DateTimeImmutable($transaction->getDate()))->setTime(0, 0);
                if ($date < $big_bang_date) {
                    continue;
                }
            }

            $transaction->setIsOnbadge(false)
                ->setDate(date('Y-m-d'));
            $saver->addToPersist($transaction);
        }

        return $saver->persistTransactions();
    }
}
