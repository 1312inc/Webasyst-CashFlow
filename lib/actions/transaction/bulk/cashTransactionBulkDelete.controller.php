<?php

/**
 * Class cashTransactionBulkDeleteController
 */
class cashTransactionBulkDeleteController extends cashJsonController
{
    public function execute()
    {
        $moveData = waRequest::post('delete', [], waRequest::TYPE_ARRAY_TRIM);
        if (!$moveData) {
            $this->setError('No data');

            return;
        }
        $ids = explode(',', $moveData['ids']);
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($ids);

        /** @var cashTransaction $transaction */
        foreach ($transactions as $transaction) {
            $transaction->setIsArchived(1);
            cash()->getEntityPersister()->update($transaction);
        }
    }
}
