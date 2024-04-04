<?php

abstract class cashBusinessPlugin extends waPlugin
{
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
     *     'description'    => 'abracadabra'
     *     'hash'           => '64be58f9-c7fc-0027-96ba-774ec55a1111'
     * ]
     * @param array $transactions
     * @return array
     * @throws waException
     */
    protected function addTransactionsByAccount($transactions)
    {
        if (!empty($transactions) && is_array($transactions)) {
            $add_transactions = [];
            $now = date('Y-m-d H:i:s');
            $transaction_model = cash()->getModel(cashTransaction::class);
            $create_contact_id = wa()->getUser()->getId();
            $external_source = $this->getExternalSource();
            $hashs = array_column($transactions, 'hash');
            $transaction_in_db = $transaction_model
                ->where('external_hash IN (?)', $hashs)
                ->where('is_archived = 0')->fetchAll();
            $skip_hashs = array_column($transaction_in_db, 'external_hash');
            foreach ($transactions as $_transaction) {
                $external_hash = ifset($_transaction, 'hash', '');
                if (in_array($external_hash, $skip_hashs)) {
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
                    'description'       => ifset($_transaction, 'description', ''),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'external_source'   => $external_source,
                    'external_hash'     => $external_hash
                ];
            }
            if (!empty($add_transactions)) {
                $transaction_model->multipleInsert($add_transactions);
            }

            return $add_transactions;
        }

        return [];
    }
}
