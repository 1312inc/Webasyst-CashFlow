<?php

/**
 * Class cashContactRightsManager
 */
class cashContactRightsManager
{
    /**
     * @var waContactRightsModel
     */
    private $model;

    /**
     * @var cashContactRights[]
     */
    private $accesses = [];

    /**
     * cashContactRightsService constructor.
     */
    public function __construct()
    {
        $this->model = new waContactRightsModel();
    }

    /**
     * @param waDbQuery $query
     * @param waContact $contact
     *
     * @return waDbQuery
     * @throws waException
     */
    public function filterQueryCategoriesForContact(waDbQuery $query, waContact $contact): waDbQuery
    {
        if ($this->isAdmin($contact)) {
            return $query;
        }

        $query->where('id in (i:ids)', ['ids' => $this->getCategoryIdsForContact($contact)]);

        return $query;
    }

    /**
     * @param waDbQuery $query
     * @param waContact $contact
     *
     * @return waDbQuery
     * @throws waException
     */
    public function filterQueryAccountsForContact(waDbQuery $query, waContact $contact): waDbQuery
    {
        if ($this->isAdmin($contact)) {
            return $query;
        }

        $query->where('id in (i:ids)', ['ids' => $this->getAccountIdsForContact($contact)]);

        return $query;
    }

    /**
     * @return array
     */
    public function getUserIdsWithAccess(): array
    {
        return $this->model->getUsers(cashConfig::APP_ID);
    }

    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function isAdmin(waContact $contact): bool
    {
        return $this->getContactAccess($contact)->isAdmin();
    }

    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function isRoot(waContact $contact): bool
    {
        return $this->getContactAccess($contact)->isRoot();
    }

    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function hasAccessToApp(waContact $contact): bool
    {
        return $this->getContactAccess($contact)->hasAccessToApp();
    }

    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function canImport(waContact $contact): bool
    {
        return $this->getContactAccess($contact)->canImport();
    }

    /**
     * @param waContact $contact
     *
     * @return bool
     */
    public function canSeeReport(waContact $contact): bool
    {
        return $this->getContactAccess($contact)->canSeeReport();
    }

    /**
     * @param waContact $contact
     * @param           $accountId
     *
     * @return bool
     */
    public function hasFullAccessToAccount(waContact $contact, $accountId): bool
    {
        if ($this->getContactAccess($contact)->isAdmin()) {
            return true;
        }

        return in_array(
            (int)$accountId,
            $this->getContactAccess($contact)->getAccountIdsWithAccess(cashRightConfig::ACCOUNT_FULL_ACCESS),
            true
        );
    }

    /**
     * @param waContact $contact
     * @param           $accountId
     *
     * @return bool
     */
    public function canAddTransactionToAccount(waContact $contact, $accountId): bool
    {
        if ($this->getContactAccess($contact)->isAdmin()) {
            return true;
        }

        return in_array(
            (int)$accountId,
            $this->getContactAccess($contact)->getAccountIdsWithAccess(
                cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
            ),
            true
        );
    }

    /**
     * @param waContact       $contact
     * @param cashTransaction $transaction
     *
     * @return bool
     */
    public function canEditOrDeleteTransaction(waContact $contact, cashTransaction $transaction): bool
    {
        if ($this->getContactAccess($contact)->isAdmin()) {
            return true;
        }

        if (!in_array(
            (int)$transaction->getCategoryId(),
            $this->getContactAccess($contact)->getCategoriesIdsWithAccess(cashRightConfig::CATEGORY_FULL_ACCESS),
            true
        )) {
            return false;
        }

        if ($transaction->getCreateContactId() != $contact->getId()) {
            return in_array(
                (int)$transaction->getAccountId(),
                $this->getContactAccess($contact)->getAccountIdsWithAccess(
                    cashRightConfig::ACCOUNT_ADD_EDIT_VIEW_TRANSACTIONS_CREATED_BY_OTHERS
                ),
                true
            );
        }

        return in_array(
            (int)$transaction->getAccountId(),
            $this->getContactAccess($contact)->getAccountIdsWithAccess(
                cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
            ),
            true
        );
    }

    /**
     * @param waContact       $contact
     * @param cashTransaction $transaction
     *
     * @return bool
     */
    public function canAddTransaction(waContact $contact, cashTransaction $transaction): bool
    {
        if ($this->getContactAccess($contact)->isAdmin()) {
            return true;
        }

        if ($transaction->getCategoryId() && !in_array(
                (int)$transaction->getCategoryId(),
                $this->getContactAccess($contact)->getCategoriesIdsWithAccess(cashRightConfig::CATEGORY_FULL_ACCESS),
                true
            )) {
            return false;
        }

        if ($transaction->getAccountId() && !in_array(
                (int)$transaction->getAccountId(),
                $this->getContactAccess($contact)->getAccountIdsWithAccess(
                    cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
                ),
                true
            )) {
            return false;
        }

        return true;
    }

    /**
     * @param waContact $contact
     * @param           $categoryId
     *
     * @return bool
     */
    public function hasFullAccessToCategory(waContact $contact, $categoryId): bool
    {
        if ($this->getContactAccess($contact)->isAdmin()) {
            return true;
        }

        return in_array(
            (int)$categoryId,
            $this->getContactAccess($contact)->getCategoriesIdsWithAccess(cashRightConfig::CATEGORY_FULL_ACCESS),
            true
        );
    }

    /**
     * @param waContact $contact
     *
     * @return array|bool|int
     */
    public function getCategoryIdsForContact(waContact $contact)
    {
        return $this->getContactAccess($contact)
            ->getCategoriesIdsWithAccess(cashRightConfig::CATEGORY_FULL_ACCESS);
    }

    /**
     * @param waContact $contact
     * @param string    $alias
     * @param string    $field
     *
     * @return string
     * @throws waException
     */
    public function getSqlForCategoryJoin(waContact $contact, $alias = 'cc', $field = 'id'): string
    {
        if ($this->isAdmin($contact)) {
            return ' 1 /* categories access */';
        }

        $ids = $this->getCategoryIdsForContact($contact);
        $query = $this->model->prepare(sprintf(' %s.%s in (i:ids) /* categories access */', $alias, $field));
        $query->bindArray(['ids' => $ids]);

        return $query->getQuery();
    }

    /**
     * @param waContact $contact
     * @param string    $alias
     * @param string    $field
     *
     * @return string
     * @throws waException
     */
    public function getSqlForAccountJoinWithMinimumAccess(waContact $contact, $alias = 'ca', $field = 'id'): string
    {
        return $this->getSqlForAccountJoinWith(
            $contact,
            $alias,
            $field,
            cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
        );
    }

    /**
     * @param waContact $contact
     * @param string    $alias
     * @param string    $field
     *
     * @return string
     * @throws waException
     */
    public function getSqlForAccountJoinWithFullAccess(waContact $contact, $alias = 'ca', $field = 'id'): string
    {
        return $this->getSqlForAccountJoinWith($contact, $alias, $field, cashRightConfig::ACCOUNT_FULL_ACCESS);
    }

    /**
     * @param waContact $contact
     *
     * @param int       $access
     *
     * @return array
     */
    public function getAccountIdsForContact(
        waContact $contact,
        $access = cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
    ): array {
        return $this->getContactAccess($contact)
            ->getAccountIdsWithAccess($access);
    }

    /**
     * @param waContact $contact
     * @param string    $alias
     * @param string    $field
     * @param int       $access
     *
     * @return string
     * @throws waException
     */
    private function getSqlForAccountJoinWith(
        waContact $contact,
        $alias = 'ca',
        $field = 'id',
        $access = cashRightConfig::ACCOUNT_ADD_EDIT_SELF_CREATED_TRANSACTIONS_ONLY
    ): string {
        if ($this->isAdmin($contact)) {
            return ' 1 /* account access */';
        }

        $ids = $this->getAccountIdsForContact($contact, $access);
        $query = $this->model->prepare(sprintf(' %s.%s in (i:ids)  /* account access */', $alias, $field));
        $query->bindArray(['ids' => $ids]);

        return $query->getQuery();
    }

    /**
     * @param waContact $contact
     *
     * @return cashContactRights
     */
    private function getContactAccess(waContact $contact): cashContactRights
    {
        if (!isset($this->accesses[$contact->getId()])) {
            $this->accesses[$contact->getId()] = new cashContactRights($contact->getRights(cashConfig::APP_ID));
        }

        return $this->accesses[$contact->getId()];
    }
}