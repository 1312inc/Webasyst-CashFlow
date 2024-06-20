<?php

class cashTransactionDataModel extends cashModel
{
    protected $table = 'cash_transaction_data';

    /**
     * @param $transaction_data
     * format $data[] = [
     *      transaction_id => [
     *          field_id => value
     *          ...
     *          field_id => value
     *      ]
     * ]
     */
    public function multipleInsertData($transaction_data = [])
    {
        $insert_data = [];
        foreach ((array) $transaction_data as $_transaction_id => $_data) {
            foreach ((array) $_data as $field => $value) {
                $insert_data[] = [
                    'transaction_id' => (int) $_transaction_id,
                    'field_id' => (string) $field,
                    'value'  => (string) $value
                ];
            }
        }

        return ($insert_data ? parent::multipleInsert($insert_data) : true);
    }
}
