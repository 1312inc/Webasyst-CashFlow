<?php

/**
 * Class cashCategoryModel
 */
class cashCategoryModel extends cashModel
{
    protected $table = 'cash_category';

    /**
     * @param string $type
     *
     * @return array
     */
    public function getByType($type)
    {
        return $this
            ->select('*')
            ->where('`type` = s:type', ['type' => $type])
            ->order('sort ASC, id DESC')
            ->fetchAll();
    }

    /**
     * @return array
     */
    public function getAllActive()
    {
        return $this
            ->select('*')
            ->order('sort ASC, id DESC')
            ->fetchAll('id');
    }
}
