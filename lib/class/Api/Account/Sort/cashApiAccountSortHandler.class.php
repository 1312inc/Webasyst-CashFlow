<?php

/**
 * Class cashApiAccountSortHandler
 */
class cashApiAccountSortHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAccountSortRequest $request
     *
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can sort accounts'));
        }

        $saver = new cashAccountSaver();
        if (!$saver->sort($request->order)) {
            throw new kmwaRuntimeException($saver->getError());
        }
    }
}
