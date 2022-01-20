<?php

class cashApiTransactionRestoreHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionRestoreRequest $request
     *
     * @return cashApiTransactionRestoreDto
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function handle($request)
    {
        if (!wa()->getUser()->isAdmin(cashConfig::APP_ID)) {
            throw new kmwaForbiddenException(_w('You are not allowed to restore transactions'));
        }

        $result = [[], []];
        /** @var array<cashTransaction> $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->getIds());
        if (!$transactions) {
            return new cashApiTransactionRestoreDto([], []);
        }

        $repeater = new cashTransactionRepeater();
        $transactionModel = cash()->getModel(cashTransaction::class);
        foreach ($transactions as $transaction) {
            if (!$transaction->getIsArchived()) {
                continue;
            }

            cash()->getModel()->startTransaction();
            try {
                $transaction->setIsArchived(false);
                cash()->getEntityPersister()->update($transaction);

                /** @var cashRepeatingTransaction $repeatingTransaction */
                $repeatingTransaction = $transaction->getRepeatingTransaction();
                if ($repeatingTransaction
                    && !$transactionModel->getAllRepeatingIdsAfterTransaction(
                        $repeatingTransaction->getId(),
                        $transaction->getId()
                    )
                ) {
                    $repeater->repeat(
                        $repeatingTransaction,
                        DateTime::createFromFormat('Y-m-d', $transaction->getDate()),
                        true
                    );

                    if ((new cashShopIntegration())->isShopForecastRepeatingTransaction($repeatingTransaction)) {
                        $repeatingTransaction->setEnabled(1);
                        cash()->getEntityPersister()->update($repeatingTransaction);
                    }
                }

                cash()->getModel()->commit();

                $result[0][] = $transaction->getId();
            } catch (Exception $exception) {
                cash()->getModel()->rollback();
                $result[1][] = $transaction->getId();

                cash()->getLogger()->error(sprintf('Error in restore %s', $transaction->getId()), $exception);
            }
        }

        return new cashApiTransactionRestoreDto($result[0], $result[1]);
    }
}
