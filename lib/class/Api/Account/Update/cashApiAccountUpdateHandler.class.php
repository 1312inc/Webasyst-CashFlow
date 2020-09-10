<?php

/**
 * Class cashApiAccountUpdateHandler
 */
class cashApiAccountUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountUpdateRequest $request
     *
     * @return cashAccount|cashApiAccountResponse|cashApiErrorResponse
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

        $saver = new cashAccountSaver();
        $data = (array) $request;
        if ($saver->saveFromArray($account, $data)) {
            return cashApiAccountResponse::fromAccount($account);
        }

        return new cashApiErrorResponse($saver->getError());
    }
}
