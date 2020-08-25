<?php

/**
 * Class cashAccountRepository
 *
 * @method cashAccountModel getModel()
 */
class cashAccountRepository extends cashBaseRepository
{
    protected $entity = cashAccount::class;

    /**
     * @param waContact|null $contact
     *
     * @return cashAccount[]
     * @throws waException
     */
    public function findAllActiveForContact(waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->generateWithData($this->getModel()->getAllActiveForContact($contact), true);
    }

    /**
     * @param waContact|null $contact
     *
     * @return cashAccount
     * @throws waException
     */
    public function findFirstForContact(waContact $contact = null): cashAccount
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->findByQuery(
            cash()->getContactRights()->filterQueryAccountsForContact(
                $this->getModel()
                    ->select('*')
                    ->order('id')
                    ->limit(1),
                $contact
            ),
            false
        );
    }
}
