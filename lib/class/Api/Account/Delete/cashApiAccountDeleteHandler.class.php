<?php

/**
 * Class cashApiAccountDeleteHandler
 */
class cashApiAccountDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountDeleteRequest $request
     *
     * @return array|cashApiAccountDeleteResponse|cashApiErrorResponse
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function handle($request)
    {
        /** @var cashAccountRepository $repository */
        $repository = cash()->getEntityRepository(cashAccount::class);
        $account = $repository->findById($request->id);
        if (!$account) {
            return new cashApiErrorResponse(_w('No account'));
        }

        if (!cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account)) {
            return new cashApiErrorResponse(_w('You have no access to this account'));
        }

        /** @var cashTransactionModel $model */
        $model = $repository->getModel();
        $model->startTransaction();
        try {
            $model->archiveByAccountId($account->getId());
            $account->setIsArchived(true);

            if (!cash()->getEntityPersister()->update($account)) {
                $model->rollback();

                return new cashApiErrorResponse(_w('Error while updating account'));
            }

            $model->commit();

            return new cashApiAccountDeleteResponse();
        } catch (Exception $ex) {
            $model->rollback();

            throw $ex;
        }
    }
}
