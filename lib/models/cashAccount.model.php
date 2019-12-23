<?php

/**
 * Class cashAccountModel
 */
class cashAccountModel extends cashModel
{
    protected $table = 'cash_account';

    /**
     * @return array
     */
    public function getAllActive()
    {
        return $this
            ->select('*')
            ->where('is_archived = 0')
            ->order('sort ASC, id DESC')
            ->fetchAll();
    }
}
