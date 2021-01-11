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
     * @param int            $access
     *
     * @return cashAccount[]
     * @throws waException
     */
    public function findAllActiveForContact(
        waContact $contact = null,
        $access = cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
    ): array {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->generateWithData($this->getModel()->getAllActiveForContact($contact, $access), true);
    }

    /**
     * @param waContact|null $contact
     *
     * @return cashAccount[]
     * @throws waException
     */
    public function findAllActiveFullAccessForContact(waContact $contact = null): array {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->findAllActiveForContact($contact, cashRightConfig::ACCOUNT_FULL_ACCESS);
    }

    /**
     * @param int            $id
     * @param waContact|null $contact
     *
     * @return cashAccount|null
     * @throws waException
     */
    public function findByIdForContact($id, waContact $contact = null): ?cashAccount
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->generateWithData($this->getModel()->getByIdForContact($id, $contact));
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
