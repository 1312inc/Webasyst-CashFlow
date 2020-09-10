<?php

/**
 * Class cashApiAccountCreateHandler
 */
class cashApiAccountCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountCreateRequest $request
     *
     * @return cashAccount|cashApiAccountResponse|cashApiErrorResponse
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function handle($request)
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            return new cashApiErrorResponse(_w('You can not create any account'));
        }

        /** @var cashAccountFactory $repository */
        $factory = cash()->getEntityFactory(cashAccount::class);
        $account = $factory->createNew();

        $saver = new cashAccountSaver();
        $data = (array) $request;
        $data['customer_contact_id'] = wa()->getUser()->getId();

        if ($saver->saveFromArray($account, $data)) {
            return cashApiAccountResponse::fromAccount($account);
        }

        return new cashApiErrorResponse($saver->getError());
    }
}
