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
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($this->getId());
        kmwaAssert::instance($transaction, cashTransaction::class);

        if (!cash()->getEntityPersister()->delete($transaction)) {
            $this->errors[] = _w('Error while deleting transaction');
        }
    }
}
