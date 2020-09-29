<?php

/**
 * Class cashCategoryModel
 */
class cashCategoryModel extends cashModel
{
    protected $table = 'cash_category';

    /**
     * @param string         $type
     *
     * @param waContact|null $contact
     *
     * @return array
     * @throws waException
     */
    public function getByTypeForContact($type, waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return cash()->getContactRights()->filterQueryCategoriesForContact(
            $this
                ->select('*')
                ->where('`type` = s:type', ['type' => $type])
                ->order('sort ASC, id DESC'),
            $contact
        )->fetchAll();
    }

    /**
     * @param waContact|null $contact
     *
     * @return array
     * @throws waException
     */
    public function getAllActiveForContact(waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return cash()->getContactRights()->filterQueryCategoriesForContact(
            $this
                ->select('*')
                ->order('sort ASC, id DESC'),
            $contact
        )->fetchAll('id');
    }
}
