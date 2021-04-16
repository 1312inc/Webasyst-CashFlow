<?php

/**
 * Class cashApiAccountUpdateHandler
 */
class cashApiAccountUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountUpdateRequest $request
     *
     * @return array|cashApiAccountResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashAccountRepository $repository */
        $repository = cash()->getEntityRepository(cashAccount::class);
        $account = $repository->findById($request->id);
        if (!$account) {
            throw new kmwaNotFoundException(_w('No account'));
        }

        if (!cash()->getContactRights()->hasFullAccessToAccount(wa()->getUser(), $account)) {
            throw new kmwaForbiddenException(_w('You have no access to this account'));
        }

        $saver = new cashAccountSaver();
        $data = (array) $request;
        if ($saver->saveFromArray($account, $data)) {
            return cashApiAccountResponseDto::fromAccount($account);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
