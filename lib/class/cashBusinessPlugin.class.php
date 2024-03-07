<?php

abstract class cashBusinessPlugin extends waPlugin
{
    public function getConfigParam($param = null)
    {
        static $config = null;
        if (is_null($config)) {
            $config_path = wa()->getAppPath('plugins/'.$this->getId(), 'cash').'/lib/config/config.php';
            $config = (file_exists($config_path) ? include $config_path : []);
        }

        return (is_null($param) ? $config : ifset($config, $param, null));
    }

    /**
     * format $transactions[] = [
     *     'date_operation' => '2024-01-10 09:00:00',
     *     'category_id'    => 15,
     *     'amount'         => -123.6584,
     *     'description'    => 'abracadabra'
     *     'hash'           => '64be58f9-c7fc-0027-96ba-774ec55a1111'
     * ]
     * @param int $cash_account_id
     * @param array $transactions
     * @return array
     * @throws waException
     */
    protected function addTransactionsByAccount($cash_account_id, $transactions)
    {
        if (!empty($transactions) && is_array($transactions)) {
            $now = date('Y-m-d H:i:s');
            $transaction_model = cash()->getModel(cashTransaction::class);
            $create_contact_id = wa()->getUser()->getId();
            $external_source = 'api_'.$this->getId();
            foreach ($transactions as &$_transaction) {
                $amount = (float) ifset($_transaction, 'amount', 0);
                $is_credit = $amount > 0;
                $date_operation = strtotime(ifset($_transaction, 'date_operation', $now));
                $_transaction = [
                    'date'              => date('Y-m-d', $date_operation),
                    'datetime'          => date('Y-m-d H:i:s', $date_operation),
                    'account_id'        => (int) $cash_account_id,
                    'category_id'       => (int) ifset($_transaction, 'category', ($is_credit ? -2 : -1)),
                    'amount'            => $amount,
                    'description'       => ifset($_transaction, 'description', ''),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'external_source'   => $external_source,
                    'external_hash'     => ifset($_transaction, 'hash', '')
                ];
            }
            $transaction_model->multipleInsert($transactions);
            return $transactions;
        }

        return [];
    }
}
