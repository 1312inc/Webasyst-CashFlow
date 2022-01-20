<?php

class cashApiTransactionPurgeHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionPurgeRequest $request
     *
     * @return cashApiTransactionPurgeDto
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function handle($request)
    {
        if (!wa()->getUser()->isAdmin(cashConfig::APP_ID)) {
            throw new kmwaForbiddenException(_w('You are not allowed to delete restore transactions'));
        }

        $res = [[], []];

        /** @var cashTransaction[] $transactions */
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($request->getIds());
        if (!$transactions) {
            return new cashApiTransactionPurgeDto($res[0], $res[1]);
        }

        $persister = cash()->getEntityPersister();

        cash()->getModel()->startTransaction();
        try {
            foreach ($transactions as $transaction) {
                if (!$transaction->getIsArchived()) {
                    $res[1][] = $transaction->getId();
                    continue;
                }

                $persister->delete($transaction);
                $res[0][] = $transaction->getId();
            }

            cash()->getModel()->commit();
        } catch (Exception $exception) {
            cash()->getModel()->rollback();
        }

        return new cashApiTransactionPurgeDto($res[0], $res[1]);
    }
}
