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
        return 'api_'.$this->getId();
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
     *     'import_id'      => 2,
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
            $add_ids = [];
            $update_ids = [];
            $add_transactions = [];
            $now = date('Y-m-d H:i:s');
            $transaction_model = cash()->getModel(cashTransaction::class);
            $create_contact_id = wa()->getUser()->getId();
            $external_source = $this->getExternalSource();
            $transactions = $this->autoMappingPilot($transactions);
            foreach ($transactions as $_transaction) {
                $transaction_in_db = [];
                $external_hash = ifset($_transaction, 'hash', null);
                $amount = (float) ifset($_transaction, 'amount', 0);
                $is_credit = $amount > 0;
                $date_operation = strtotime(ifset($_transaction, 'date_operation', $now));
                $add_transaction = [
                    'date'              => date('Y-m-d', $date_operation),
                    'datetime'          => date('Y-m-d H:i:s', $date_operation),
                    'account_id'        => $this->cash_account_id,
                    'category_id'       => (int) ifset($_transaction, 'category_id', ($is_credit ? -2 : -1)),
                    'amount'            => $amount,
                    'description'       => ifset($_transaction, 'description', null),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'import_id'         => ifempty($_transaction, 'import_id', null),
                    'external_source'   => $external_source,
                    'external_hash'     => $external_hash,
                    'external_data'     => empty($_transaction['external_data']) ? null : json_encode((array) $_transaction['external_data']),
                    'contractor_contact_id' => ifset($_transaction, 'contractor_id', null)
                ];

                if ($external_hash) {
                    $transaction_in_db = $transaction_model->getByField([
                        'external_hash' => $external_hash,
                        'external_source' => $external_source
                    ]);
                }
                if ($transaction_in_db) {
                    // UPDATE
                    try {
                        if ($transaction_model->updateById($transaction_in_db['id'], $add_transaction)) {
                            $update_ids[] = $transaction_in_db['id'];
                            $add_transaction['id'] = $transaction_in_db['id'];
                        }
                    } catch (Exception $e) {
                        cash()->getLogger()->error('Error update business transaction', $e);
                    }
                } else {
                    // INSERT
                    try {
                        $db_result = $transaction_model->insert($add_transaction);
                        if (is_int($db_result)) {
                            $add_ids[] = $db_result;
                            $add_transaction['id'] = $db_result;
                        }
                    } catch (Exception $e) {
                        cash()->getLogger()->error('Error insert business transaction', $e);
                    }
                }

                if ($add_transaction['id']) {
                    $add_transactions[] = $add_transaction;
                    if ($_data = ifset($_transaction, 'data', null)) {
                        $data[$add_transaction['id']] = $_data;
                    }
                }

            }

            if ($update_ids) {
                cashAutomation::automationEvent($this, array_column($update_ids, 'id'), 'update');
            }
            if ($add_ids) {
                cashAutomation::automationEvent($this, array_column($add_ids, 'id'), 'add');
            }

            if ($data) {
                $data_model->multipleInsertData($data);
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
