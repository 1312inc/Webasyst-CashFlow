<?php

final class cashApiCategorySortHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategorySortRequest $request
     *
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can sort categories'));
        }

        $saver = new cashCategorySaver();
        if (!$saver->sort($request->getOrder())) {
            throw new kmwaRuntimeException($saver->getError());
        }
        $saver->resort();
    }
}
