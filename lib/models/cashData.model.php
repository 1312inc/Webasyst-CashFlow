<?php

class cashDataModel extends cashModel
{
    protected $table = 'cash_data';

    /**
     * @param $data
     * format $data[] = [
     *      sub_id => [
     *          name => value
     *          ...
     *          name => value
     *      ]
     * ]
     */
    public function multipleInsert($data)
    {
        $insert_data = [];
        foreach ((array) $data as $sub_id => $_data) {
            foreach ((array) $_data as $name => $value) {
                $insert_data[] = [
                    'sub_id' => $sub_id,
                    'name'   => (string) $name,
                    'value'  => (string) $value
                ];
            }
        }

        return ($insert_data ? parent::multipleInsert($insert_data) : true);
    }

    public function deleteBySubId($sub_ids)
    {
        if ($sub_ids) {
            return $this->exec("
                DELETE FROM ".$this->table." WHERE sub_id IN (s:sub_ids)
            ", ['sub_ids' => (array) $sub_ids]);
        }

        return true;
    }
}
