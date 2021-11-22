<?php

final class cashApiAccountUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountUpdateRequest $request
     *
     * @return array|cashApiAccountResponseDto
     *
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $repository = cash()->getEntityRepository(cashAccount::class);
        /** @var cashAccount $account */
        $account = $repository->findById($request->getId());
        if (!$account) {
            throw new kmwaNotFoundException(_w('No account'));
        }

        if (!cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account)) {
            throw new kmwaForbiddenException(_w('You have no access to this account'));
        }

        $saver = new cashAccountSaver();
        if ($saver->saveFromApi($account, $request)) {
            return cashApiAccountResponseDto::fromAccount($account);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
