<?php

/**
 * Class cashApiAccountDeleteHandler
 */
class cashApiAccountDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountDeleteRequest $request
     *
     * @return bool
     * @throws waException
     * @throws kmwaNotFoundException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     */
    public function handle($request)
    {
        /** @var cashAccountRepository $repository */
        $repository = cash()->getEntityRepository(cashAccount::class);

        /** @var cashAccount $account */
        $account = $repository->findById($request->id);
        if (!$account || $account->getIsArchived()) {
            throw new kmwaNotFoundException(_w('No account'));
        }

        if (!cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account)) {
            throw new kmwaForbiddenException(_w('You have no access to this account'));
        }

        /** @var cashTransactionModel $transactionModel */
        $transactionModel = cash()->getModel(cashTransaction::class);
        $transactionModel->startTransaction();
        try {
            $transactionModel->archiveByAccountId($account->getId());
            $account->setIsArchived(true);

            if (!cash()->getEntityPersister()->update($account)) {
                $transactionModel->rollback();

                throw new kmwaRuntimeException(_w('Error while updating account'));
            }

            $transactionModel->commit();

            return true;
        } catch (Exception $ex) {
            $transactionModel->rollback();

            throw $ex;
        }
    }
}
