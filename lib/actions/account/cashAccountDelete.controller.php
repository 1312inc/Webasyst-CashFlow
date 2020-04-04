<?php

/**
 * Class cashAccountDeleteController
 */
class cashAccountDeleteController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->getId());
        kmwaAssert::instance($account, cashAccount::class);

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();
        try {
            $model->archiveByAccountId($account->getId());
            $account->setIsArchived(true);
            if (!cash()->getEntityPersister()->update($account)) {
                throw new kmwaRuntimeException(_w('Error while updating account'));
            }

            $model->commit();
        } catch (Exception $ex) {
            $model->rollback();
            $this->errors[] = $ex->getMessage();
        }

    }
}
