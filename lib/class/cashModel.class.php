<?php

/**
 * Class cashModel
 */
class cashModel extends waModel
{
    /**
     * @var int
     */
    private $autocommitMode = 0;

    /**
     * @param int $flag
     */
    public function autocommit($flag)
    {
        $this->exec('set autocommit = '.(int)$flag);
    }

    public function startTransaction()
    {
        $this->autocommitMode = $this->query('SELECT @@autocommit')->fetchField();
        $this->autocommit(0);
        $this->exec('start transaction');
    }

    public function commit()
    {
        $this->exec('commit');
        $this->autocommit($this->autocommitMode);
    }

    public function rollback()
    {
        $this->exec('rollback');
        $this->autocommit($this->autocommitMode);
    }
}
