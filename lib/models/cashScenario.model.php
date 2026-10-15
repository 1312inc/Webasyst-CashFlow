<?php

/**
 * cashScenarioModel
 */
class cashScenarioModel extends cashModel
{
    protected $table = 'cash_scenario';

    public function getAll($key = null, $normalize = false)
    {
        return $this->query("
            SELECT * FROM ".$this->table."
            ORDER BY sort ASC, id DESC"
        )->fetchAll($key, $normalize);
    }
}
