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
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
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
        $data = (array) $request;
        $data['customer_contact_id'] = wa()->getUser()->getId();

        if ($saver->saveFromArray($account, $data)) {
            return cashApiAccountResponseDto::fromAccount($account);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
