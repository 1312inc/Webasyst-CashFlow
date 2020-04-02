<?php

/**
 * Class cashImportModel
 */
class cashImportModel extends cashModel
{
    protected $table = 'cash_import';

    /**
     * @param int $importId
     *
     * @return array
     */
    public function getDateBounds($importId)
    {
        $sql = <<<SQL
select min(ct.date) min_date, max(ct.date) max_date  
from cash_transaction ct 
    join cash_import ci on ct.import_id = ci.id
where ci.id = i:import_id
SQL;

        $bounds = $this->query($sql, ['import_id' => $importId])->fetchAll();
        $bounds = reset($bounds);
        if (count($bounds) === 2) {
            return [$bounds['min_date'], $bounds['max_date']];
        }

        return [date('Y-m-d'), date('Y-m-d')];
    }
}
