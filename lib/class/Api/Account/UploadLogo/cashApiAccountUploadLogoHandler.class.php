<?php

/**
 * Class cashApiAccountUploadLogoHandler
 */
class cashApiAccountUploadLogoHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountUploadLogoRequest $request
     *
     * @return string
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $account = cash()->getEntityRepository(cashAccount::class)->findByIdForContact((int) $request->account_id);
        if (!$account) {
            throw new kmwaNotFoundException(_w('No account'));
        }

        if ((new cashLogoUploader())->uploadAndSaveToAccount($account, $request->file)) {
            cash()->getEntityPersister()->save($account);
        } else {
            throw new kmwaRuntimeException('Error on save logo');
        }

        return $account->getIcon();
    }
}
