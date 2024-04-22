<?php

abstract class cashBusinessPlugin extends waPlugin
{
    const AUTO_MAPPING_FLAG = 'A';

    /** @var int ID счета */
    protected int $cash_account_id;

    public function __construct($info)
    {
        parent::__construct($info);
        $this->cash_account_id = (int) ifset($info, 'cash_account', 0);
    }

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
     * @return array
     * @throws waException
     */
    public static function getListPlugins()
    {
        $plugins = [];
        foreach ((array) wa()->getConfig()->getPlugins() as $_plugin) {
            if (!empty($_plugin['import_api'])) {
                $plugins[] = $_plugin;
            }
        }

        return $plugins;
    }

    /**
     * @return string
     */
    public function getExternalSource()
    {
        return 'api_'.$this->getId().'_'.$this->cash_account_id;
    }

    /**
     * @param $external_source
     * @return string
     */
    protected static function getPluginIdByExternalSource($external_source = '')
    {
        if (empty($external_source) || !$source = explode('_', $external_source)) {
            return '';
        }

        return ifset($source, 1, '');
    }

    /**
     * format $transactions[] = [
     *     'date_operation' => '2024-01-10 09:00:00',
     *     'category_id'    => 15,
     *     'amount'         => -123.6584,
     *     'description'    => 'abracadabra',
     *     'hash'           => '64be58f9-c7fc-0027-96ba-774ec55a1111',
     *     'contractor_id'  => 45,
     *     'external_data'  => [],
     *     'data'           => [],
     * ]
     * @param array $transactions
     * @return array
     * @throws waException
     */
    protected function addTransactionsByAccount($transactions)
    {
        static $data_model;
        if (!$data_model) {
            /** @var cashTransactionDataModel $data_model */
            $data_model = cash()->getModel('cashTransactionData');
        }
        if (!empty($transactions) && is_array($transactions)) {
            $data = [];
            $add_transactions = [];
            $now = date('Y-m-d H:i:s');
            $transaction_model = cash()->getModel(cashTransaction::class);
            $create_contact_id = wa()->getUser()->getId();
            $external_source = $this->getExternalSource();
            $hashes = array_column($transactions, 'hash');
            $transaction_in_db = $transaction_model
                ->where('external_hash IN (?)', $hashes)
                ->where('external_source = ?', $external_source)
                ->where('is_archived = 0')->fetchAll();
            $skip_hashes = array_column($transaction_in_db, 'external_hash');
waLog::dump(['ADDTRANSACTIONSBYACCOUNT-1', '$transactions' => $transactions]);
            $transactions = $this->autoMappingPilot($transactions);
            foreach ($transactions as $_transaction) {
                $external_hash = ifset($_transaction, 'hash', null);
                if (in_array($external_hash, $skip_hashes)) {
                    continue;
                }
                $amount = (float) ifset($_transaction, 'amount', 0);
                $is_credit = $amount > 0;
                $date_operation = strtotime(ifset($_transaction, 'date_operation', $now));
                $add_transactions[] = [
                    'date'              => date('Y-m-d', $date_operation),
                    'datetime'          => date('Y-m-d H:i:s', $date_operation),
                    'account_id'        => $this->cash_account_id,
                    'category_id'       => (int) ifset($_transaction, 'category_id', ($is_credit ? -2 : -1)),
                    'amount'            => $amount,
                    'description'       => ifset($_transaction, 'description', null),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'external_source'   => $external_source,
                    'external_hash'     => $external_hash,
                    'external_data'     => empty($_transaction['external_data']) ? null : json_encode((array) $_transaction['external_data']),
                    'contractor_contact_id' => ifset($_transaction, 'contractor_id', null)
                ];
                if ($_data = ifset($_transaction, 'data', null)) {
                    $data[$external_hash] = $_data;
                }
            }
waLog::dump(['ADDTRANSACTIONSBYACCOUNT-2']);
            if (!empty($add_transactions)) {
                $db_result = $transaction_model->multipleInsert($add_transactions);
                if ($db_result->getResult() && $data) {
                    $transaction_data = [];
                    $transaction_ids = $transaction_model->select('external_hash, id')
                        ->where('external_source = ?', $external_source)
                        ->where('external_hash IN (:hash)', ['hash' => array_keys($data)])
                        ->where('is_archived = 0')
                        ->fetchAll('external_hash');
                    foreach ($data as $_external_hash => $_data) {
                        if ($transaction_id = ifempty($transaction_ids, $_external_hash, 'id', null)) {
                            $transaction_data[$transaction_id] = $_data;
                        }
                    }
                    $data_model->multipleInsertData($transaction_data);
                }
waLog::dump(['ADDTRANSACTIONSBYACCOUNT-3', '$transactions' => $transactions]);
            }

            return $add_transactions;
        }

        return [];
    }

    protected function autoMappingPilot($transactions)
    {
        foreach ($transactions as &$_transaction) {
            if ($_transaction['category_id'] === self::AUTO_MAPPING_FLAG) {
                $_transaction['category_id'] = ($_transaction['amount'] > 0 ? -2 : -1);
            }
        }

        return $transactions;
    }
}
