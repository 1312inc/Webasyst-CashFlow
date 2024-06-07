<?php

class cashTinkoffPlugin extends cashBusinessPlugin
{
    const DEFAULT_UPDATE_TIMEOUT = 60; // min
    const LIMIT_STATEMENTS = 5000;
    const DEFAULT_START_DATE = '2006-01-01 00:00:00';
    const API_URL = 'https://business.tinkoff.ru/openapi/api/';

    private bool $self_mode;
    private string $profile_id;
    private string $tinkoff_token; // for self-mode
    private string $account_number;
    private array $mapping_categories;

    public function __construct($info)
    {
        parent::__construct($info);

        /** обязательно назначаем профиль, через конструктор или setCashProfile() */
        $profile_id = ifempty($info, 'profile_id', 0);
        $this->setCashProfile($profile_id);
    }

    /**
     * @param $profile_id
     * @return $this
     */
    public function setCashProfile($profile_id)
    {
        $profile = $this->getProfile($profile_id);
        $this->profile_id = (int) $profile_id;
        $this->cash_account_id = (int) ifset($profile, 'cash_account', 0);
        $this->account_number = ifset($profile, 'account_number', '');
        $this->mapping_categories = ifset($profile, 'mapping', []);

        return $this;
    }

    private function getToken()
    {
        if (!isset($this->tinkoff_token)) {
            $this->tinkoff_token = (string) $this->getSettings('tinkoff_token');
        }

        return $this->tinkoff_token;
    }

    /**
     * https://developer.tinkoff.ru/docs/intro/manuals/
     * @return bool
     */
    private function isSelfMode()
    {
        if (!isset($this->self_mode)) {
            $this->self_mode = !!$this->getSettings('self_mode');
        }

        return $this->self_mode;
    }

    private function apiQuery($url, $headers = [], $post_fields = [])
    {
        $options = [
            'format'         => waNet::FORMAT_JSON,
            'request_format' => waNet::FORMAT_RAW
        ];
        $headers += [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer '.$this->getToken()
        ];
        try {
            $net = new waNet($options, $headers);
            try {
                $method = (empty($post_fields) ? waNet::METHOD_GET : waNet::METHOD_POST);
                $response = $net->query($url, http_build_query($post_fields), $method);
            } catch (Exception $ex) {
                $response = $net->getResponse();
                if (empty($response)) {
                    $response = ['error' => 'error_query', 'error_description' => $ex->getMessage()];
                }
                waLog::dump([$url, $response], TINKOFF_FILE_LOG);
            }
            if ($http_code = $net->getResponseHeader('http_code')) {
                $response += ['http_code' => $http_code];
            }
        } catch (Exception $exception) {
            waLog::dump([$url, $exception->getMessage()], TINKOFF_FILE_LOG);
            throw new waException($exception->getMessage());
        }

        return $response;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-4-bank-accounts
     * @param $tinkoff_id
     * @param $inn
     * @return array
     * @throws waException
     */
    public function getAccounts($tinkoff_id = null, $inn = null)
    {
        $cache = new waVarExportCache('accounts_'.$inn, 60, 'cash/plugins/tinkoff');
        if ($accounts = $cache->get()) {
            return $accounts;
        }
        if ($this->isSelfMode()) {
            $result = $this->apiQuery(self::API_URL.'v4/bank-accounts');
        } else {
            try {
                $answer = (new waServicesApi())->serviceCall('BANK', [
                    'sub_path' => 'get_accounts',
                    'tinkoff_id' => $tinkoff_id,
                    'inn' => $inn
                ]);
                $result = (array) ifset($answer, 'response', 'accounts_info', []);
            } catch (Exception $ex) {
                waLog::dump(['getAccounts', $ex->getMessage()], TINKOFF_FILE_LOG);
                $result = ['error' => 'error_statement', 'error_description' => $ex->getMessage()];
            }
        }

        if (!empty($answer['response']['error'])) {
            $result += $answer['response'];
        } else {
            $cache->set($result);
        }

        return $result;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-company
     * @param $tinkoff_id
     * @param $inn
     * @return array
     * @throws waException
     */
    public function getCompany($tinkoff_id = null, $inn = null)
    {
        if ($this->isSelfMode()) {
            return $this->apiQuery(self::API_URL.'v1/company');
        }
        try {
            $answer = (new waServicesApi())->serviceCall('BANK', [
                'sub_path' => 'get_company',
                'tinkoff_id' => $tinkoff_id,
                'inn' => $inn
            ]);
            $result = (array) ifset($answer, 'response', 'company_info', []);
        } catch (Exception $ex) {
            waLog::dump(['getCompany', $ex->getMessage()], TINKOFF_FILE_LOG);
            $result = ['error' => 'error_statement', 'error_description' => $ex->getMessage()];
        }
        if (!empty($answer['response']['error'])) {
            $result += $answer['response'];
        }

        return $result;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-statement
     * @param $tinkoff_id
     * @param $inn
     * @param $cursor
     * @param $from
     * @param $to
     * @param $limit
     * @return array
     * @throws waException
     */
    public function getStatement($tinkoff_id, $inn, $cursor, $from, $to, $limit = self::LIMIT_STATEMENTS)
    {
        $get_params = [
            'cursor' => $cursor,
            'from'   => $from,
            'to'     => $to,
            'limit'  => $limit
        ];
        if ($this->isSelfMode()) {
            $get_params += [
                'operationStatus' => 'Transaction',
                'accountNumber'   => $this->account_number,
                'withBalances'    => is_null($cursor)
            ];
            return $this->apiQuery(self::API_URL.'v1/statement?'.http_build_query($get_params));
        }

        try {
            $answer = (new waServicesApi())->serviceCall('BANK', $get_params + [
                'sub_path'       => 'get_statement',
                'tinkoff_id'     => $tinkoff_id,
                'inn'            => $inn,
                'account_number' => $this->account_number,
                'balances'       => is_null($cursor)
            ]);
            $result = (array) ifset($answer, 'response', 'statement_info', []);
        } catch (Exception $ex) {
            waLog::dump(['getStatement', $get_params, $ex->getMessage()], TINKOFF_FILE_LOG);
            $result = ['error' => 'error_statement', 'error_description' => $ex->getMessage()];
        }
        if (!empty($answer['response']['error'])) {
            $result += $answer['response'];
            if (trim($answer['response']['error']) == 'Problem with the token') {
                $this->saveProfile($this->profile_id, [
                    'status' => 'warning',
                    'status_description' => $answer['response']['error']
                ]);
            }
        }

        return $result;
    }

    /**
     * @param $transactions
     * @param $import_id
     * @return array
     * @throws waException
     */
    public function addTransactionsByAccount($transactions, $import_id = null)
    {
        if (!empty($transactions)) {
            foreach ($transactions as &$_transaction) {
                $data = [];
                $is_credit = ('Credit' == ifset($_transaction, 'typeOfOperation', 'Credit'));
                if ($category = ifset($_transaction, 'category', '')) {
                    $data['category'] = $category;
                }
                if ($receiver_inn = ifset($_transaction, 'receiver', 'inn', '')) {
                    $data['receiver_inn'] = $receiver_inn;
                }
                $_transaction = [
                    'date_operation' => ifset($_transaction, 'operationDate', date('Y-m-d H:i:s')),
                    'category_id'    => ifset($this->mapping_categories, $_transaction['category'], self::AUTO_MAPPING_FLAG),
                    'amount'         => ($is_credit ? 1 : -1) * ifset($_transaction, 'operationAmount', 0),
                    'description'    => ifset($_transaction, 'payPurpose', ''),
                    'hash'           => ifset($_transaction, 'operationId', null),
                    'import_id'      => $import_id,
                    'data'           => $data
                ];
            }

            return parent::addTransactionsByAccount($transactions);
        }

        return [];
    }

    protected function autoMappingPilot($transactions)
    {
        $transactions = $this->autoMappingPilotTransactions($transactions);

        return $this->autoMappingPilotContractors($transactions);
    }

    /**
     * Этап автоматического определения категории операции
     * на основе предыдущих добавленных операций или ключевых слов
     *
     * @param $transactions
     * @return mixed
     * @throws waDbException
     */
    private function autoMappingPilotTransactions($transactions)
    {
        static $category_model;
        if (!$category_model) {
            $category_model = cash()->getModel(cashCategory::class);
        }

        $last_categories = [];
        // Соберем категории Тинкофф последних операций для текущего аккаунта (счёта)
        $last_t_categories = $category_model->query("
            SELECT ctd.value, ct.category_id FROM cash_transaction ct
            LEFT JOIN cash_transaction_data ctd ON ctd.transaction_id = ct.id AND ctd.field_id = 'category'
            WHERE ct.account_id = i:account_id
            AND ct.external_source = s:external_source
            AND ct.external_hash IS NOT NULL
            AND ct.is_archived = 0
            ORDER BY ct.datetime
        ", ['account_id' => $this->cash_account_id, 'external_source' => $this->getExternalSource()])->fetchAll('value');
        if (!empty($last_t_categories)) {
            foreach ($last_t_categories as $_last_t_category) {
                if (
                    isset($_last_t_category['value'], $_last_t_category['category_id'])
                    && !array_key_exists($_last_t_category['value'], $last_categories)
                ) {
                    $last_categories[$_last_t_category['value']] = $_last_t_category['category_id'];
                }
            }
        }

        /** первичный скрининг */
        $missing_categories = [];
        foreach ($transactions as &$_transaction) {
            if (empty($_transaction['category_id']) || $_transaction['category_id'] === self::AUTO_MAPPING_FLAG) {
                if (isset($_transaction['data']['category'], $last_categories[$_transaction['data']['category']])) {
                    $_transaction['category_id'] = ifset($last_categories, $_transaction['data']['category'], self::AUTO_MAPPING_FLAG);
                } elseif ($missing_category = ifset($_transaction, 'data', 'category', null)) {
                    $missing_categories[$missing_category] = 0;
                }
            }
        }

        if (!empty($missing_categories)) {
            $categories = $this->getIdTinkoffCategories($missing_categories);

            /** вторичный скрининг */
            foreach ($transactions as &$_transaction) {
                $t_category = ifset($_transaction, 'data', 'category', null);
                $_transaction['category_id'] = (int) ifset($categories, $t_category, ($_transaction['amount'] > 0 ? -2 : -1));
            }
        }

        return $transactions;
    }

    /**
     * Этап автоматического определения контрагента
     * на основе полей контакта или предыдущих операций
     *
     * @param $transactions
     * @return mixed
     * @throws waDbException
     * @throws waException
     */
    private function autoMappingPilotContractors($transactions)
    {
        static $cash_model;
        $all_fields = waContactFields::getAll('all');
        if ($transactions) {
            $inns_1 = [];
            $inns = array_unique(array_column(array_column($transactions, 'data'), 'receiver_inn'));
            if (!$cash_model) {
                $cash_model = new cashModel();
            }
            if (array_key_exists('inn', $all_fields)) {
                // Соберем ИНН у контактов
                if ($inns) {
                    $inns_1 = $cash_model->query("
                        SELECT contact_id, value as inn FROM wa_contact_data wcd
                        WHERE value IN (s:inns)
                        AND field = 'inn';
                    ", ['inns' => $inns])->fetchAll('inn');
                }
            }

            // Соберем ИНН по истории ранее импортированных операций
            try {
                $inns_2 = $cash_model->query("
                    SELECT ct.contractor_contact_id AS contact_id, ctd.value AS inn FROM cash_transaction ct
                    LEFT JOIN cash_transaction_data ctd ON ctd.transaction_id = ct.id AND ctd.field_id = 'receiver_inn'
                    WHERE ct.account_id = i:account_id
                    AND ct.external_source = s:external_source
                    AND ct.is_archived = 0
                    AND ct.contractor_contact_id IS NOT NULL
                    AND ct.external_hash IS NOT NULL
                    AND ctd.value IN (s:inns)
                    ORDER BY ct.datetime DESC
                ", ['account_id' => $this->cash_account_id, 'external_source' => $this->getExternalSource(), 'inns' => $inns])->fetchAll();
            } catch (waDbException $wdb) {
                $inns_2 = [];
                waLog::dump($wdb->getMessage(), TINKOFF_FILE_LOG);
            }

            foreach ($transactions as &$_transaction) {
                $inn = ifset($_transaction, 'data', 'receiver_inn', null);
                if (array_key_exists($inn, $inns_1)) {
                    $_transaction['contractor_id'] = ifset($inns_1, $inn, 'contact_id', null);
                } else {
                    foreach ($inns_2 as $_inn_2) {
                        if ($_inn_2['inn'] == $inn) {
                            $_transaction['contractor_id'] = ifset($_inn_2, 'contact_id', null);
                            break;
                        }
                    }
                }
            }
        }

        return $transactions;
    }

    /**
     * @param $profile_id
     * @return array
     */
    public function getProfile($profile_id)
    {
        $profiles = (array) $this->getSettings('profiles');
        if ((int) $profile_id > 0) {
            return (array) ifset($profiles, $profile_id, []);
        }

        return [];
    }

    /**
     * @param $profile_id
     * @param $profile
     * @return bool
     */
    public function saveProfile($profile_id, $profile = [])
    {
        if (empty($profile) || $profile_id < 1) {
            return false;
        }
        $profiles = (array) $this->getSettings('profiles');
        $profiles[$profile_id] = $profile + ifset($profiles, $profile_id, []);
        try {
            self::saveSettings(['profiles' => $profiles]);
        } catch (Exception $e) {
            waLog::dump($e->getMessage(),TINKOFF_FILE_LOG);
            return false;
        }

        return true;
    }

    public static function getProfiles()
    {
        return (array) wa('cash')->getPlugin('tinkoff')->getSettings('profiles');
    }

    /**
     * Event api_transaction_response_external_data
     * @param $transactions
     * @param $event_name
     * @return array
     */
    public function cashEventApiTransactionExternalInfoTinkoffHandler($transactions, $event_name = null)
    {
        $result = [];

        /** @var cashApiTransactionResponseDto $_transaction */
        foreach ($transactions as $_transaction) {
            if (self::getPluginIdByExternalSource($_transaction->external_source)) {
                $result[] = new cashEventApiTransactionExternalInfoTinkoffHandler($_transaction->external_source);
            }
        }

        return $result;
    }

    /**
     * Event api_transaction.get_list
     *
     * @param $event
     * @return void
     */
    public function cashEventOnCountTinkoffHandler($event)
    {
        $profiles = self::getProfiles();
        if (empty($profiles)) {
            return;
        }

        /* определяем какой профиль запустить для обновления */
        foreach ($profiles as $profile_id => $_profile) {
            $status = ifempty($_profile, 'status', 'ok');
            $first_import = ifset($_profile, 'first_update', true);
            $update_timeout = abs((int) ifempty($_profile, 'update_timeout', self::DEFAULT_UPDATE_TIMEOUT));
            $update_time = ifempty($_profile, 'update_time', null);
            $last_update_time = ifset($_profile, 'last_update_time', 0);
            if (empty($update_time) || $status !== 'ok') {
                /** не обновляем профили, которые не импортировались успешно вручную или имеют не норм статус */
                continue;
            } elseif ($update_timeout < self::DEFAULT_UPDATE_TIMEOUT) {
                /* ставим минимальный таймаут */
                $update_timeout = self::DEFAULT_UPDATE_TIMEOUT;
            }
            if ($first_import) {
                $update_timeout = 5;
                $update_time = $last_update_time;
            }

            if (time() > ($update_time + $update_timeout * 60)) {
                /* можно обновить */
                $transaction_cli = new cashTinkoffTransactionCli();
                $transaction_cli->setInfo();
                $transaction_cli->execute($profile_id);
                break;
            }
        }
    }

    /**
     * @param $missing_categories
     * @return mixed
     * @throws waException
     */
    private function getIdTinkoffCategories($missing_categories)
    {
        $live_categories = [];
        $category_model = cash()->getModel(cashCategory::class);
        $t_map_categories = (array) $this->getSettings('t_map_categories');
        if (!empty($t_map_categories)) {
            // чистит от мертвых категорий
            $live_categories = $category_model->getById(array_values($t_map_categories));
            $t_map_categories = array_filter($t_map_categories, function ($_id) use ($live_categories) {
                return array_key_exists($_id, $live_categories);
            });
        }

        /** ID родительских категорий */
        $category_income = (int) ifempty($t_map_categories, 'parent_income', 0);
        $category_expense = (int) ifempty($t_map_categories, 'parent_expense', 0);
        if (!array_key_exists($category_income, $live_categories)) {
            // добавляем родительскую категорию
            $category_income = $category_model->insert([
                'name'  => _wp('Т - Входящие'),
                'type'  => cashCategory::TYPE_INCOME,
                'color' => '#ffdd2e',
                'sort'  => 0,
                'create_datetime' => date('Y-m-d H:i:s')
            ]);
            $t_map_categories['parent_income'] = $category_income;
        }
        if (!array_key_exists($category_expense, $live_categories)) {
            // добавляем родительскую категорию
            $category_expense = $category_model->insert([
                'name'  => _wp('Т - Исходящие'),
                'type'  => cashCategory::TYPE_EXPENSE,
                'color' => '#000000',
                'sort'  => 0,
                'create_datetime' => date('Y-m-d H:i:s')
            ]);
            $t_map_categories['parent_expense'] = $category_expense;
        }

        $add_categories = array_diff_key($missing_categories, $t_map_categories);
        if (!empty($add_categories)) {
            $income_cat_conf = $this->getConfigParam('income');
            $expense_cat_conf = $this->getConfigParam('expense');

            foreach ($add_categories as $code => $_t_category) {
                if (array_key_exists($code, $income_cat_conf)) {
                    $name = $income_cat_conf[$code];
                    $type = cashCategory::TYPE_INCOME;
                } elseif (array_key_exists($code, $expense_cat_conf)) {
                    $name = $expense_cat_conf[$code];
                    $type = cashCategory::TYPE_EXPENSE;
                } else {
                    continue;
                }
                $t_map_categories[$code] = (int) $category_model->insert([
                    'name' => $name,
                    'type' => $type,
                    'sort' => 0,
                    'color' => ($type === cashCategory::TYPE_EXPENSE ? '#000000' : '#ffdd2e'),
                    'create_datetime' => date('Y-m-d H:i:s'),
                    'category_parent_id' => ($type === cashCategory::TYPE_EXPENSE ? $category_expense : $category_income)
                ]);
            }
        }
        $this->saveSettings(['t_map_categories' => $t_map_categories]);

        return $t_map_categories;
    }
}
