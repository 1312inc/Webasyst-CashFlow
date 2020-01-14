<?php

/**
 * Class cashAccountDeleteController
 */
class cashAccountDeleteController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->getId());
        kmwaAssert::instance($account, cashAccount::class);

        $account->setIsArchived(true);
        if (!cash()->getEntityPersister()->update($account)) {
            $this->errors[] = _w('Error while updating account');
        }
    }
}
