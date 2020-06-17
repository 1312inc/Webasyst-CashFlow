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
        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($this->getId());
        kmwaAssert::instance($transaction, cashTransaction::class);

        $all = filter_var(waRequest::post('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($all) {
            /** @var cashRepeatingTransaction $repeatingTransation */
            $repeatingTransation = $transaction->getRepeatingTransaction();
            kmwaAssert::instance($repeatingTransation, cashRepeatingTransaction::class);

            /** @var cashTransactionRepository $repository */
            $repository = cash()->getEntityRepository(cashTransaction::class);
            $repository->deleteAllRepeating($repeatingTransation->getId());
            cash()->getEntityPersister()->delete($repeatingTransation);

            return;
        }

        if (!cash()->getEntityPersister()->delete($transaction)) {
            $this->errors[] = _w('Error while deleting transaction');
        }
    }
}
