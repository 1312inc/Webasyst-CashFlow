<?php

/**
 * Class cashApiAccountCreateHandler
 */
class cashApiAccountCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountCreateRequest $request
     *
     * @return cashApiAccountResponseDto
     *
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can not create any account'));
        }

        /** @var cashAccountFactory $repository */
        $factory = cash()->getEntityFactory(cashAccount::class);
        $account = $factory->createNew();

        $saver = new cashAccountSaver();
        if ($saver->saveFromApi($account, $request)) {
            return cashApiAccountResponseDto::fromAccount($account);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
