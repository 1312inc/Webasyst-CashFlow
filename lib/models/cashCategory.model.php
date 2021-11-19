<?php

class cashCategoryModel extends cashModel
{
    protected $table = 'cash_category';

    /**
     * @return array
     * @throws waException
     */
    public function getByTypeForContact(string $type, ?waContact $contact = null): array
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

    public function getChildIds(int $categoryId): array
    {
        return array_column(
            $this->select('id')
                ->where(
                    'category_parent_id = i:id',
                    ['id' => $categoryId]
                )
                ->fetchAll('id'),
            'id'
        );
    }

    public function getChildIdsWithParentIds(): array
    {
        return $this->query('SELECT id, category_parent_id FROM cash_category where category_parent_id is not null')
            ->fetchAll('id', 1);
    }

    public function getAllWithParent(): array
    {
        return $this
            ->query(
                'SELECT c.*, cp.name as parent_name 
                FROM cash_category c
                LEFT JOIN cash_category cp ON c.category_parent_id = cp.id
                ORDER BY sort'
            )
            ->fetchAll('id');
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
