<?php

class cashTinkoffPluginBackendRunController extends waLongActionController
{
    const BATCH_LIMIT = 500;

    /**
     * @return void
     * @throws waException
     */
    protected function init()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);
        $profiles = waRequest::post('profiles', [], waRequest::TYPE_ARRAY);
        $this->data = [
            'profile_id'    => $profile_id,
            'import_period' => ifset($profiles, $profile_id, 'import_period', 'all'),
            'error'         => null,
            'warning'       => null,
            'statements'    => [],
            'counter'       => 0,
            'skipped'       => 0,
            'cursor'        => '',
            'update_time'   => 0,
            'import_id'     => 0,
            'count_all_statements' => 0,
        ];

        if (empty($this->data['profile_id'])) {
            $this->data['error'] = sprintf_wp('Параметр %s обязательный', 'profile_id');
            return;
        }

        $profile = $this->plugin()->getProfile($this->data['profile_id']);
        $cash_account_id = ifset($profile, 'cash_account', null);
        if (empty($cash_account_id)) {
            $this->data['error'] = _wp('Не настроен счет импорта');
            return;
        } elseif ($cash_account_id === 'new_account') {
            $cash_account_id = $this->createAccount(ifset($profile, 'currency_code', ''));
            $this->plugin()->saveProfile($this->data['profile_id'], ['cash_account' => $cash_account_id]);
        }

        $this->data['cash_account_id'] = (int) $cash_account_id;
        $this->data['tinkoff_id'] = (string) ifset($profile, 'tinkoff_id', '');
        $this->data['company'] = (string) ifset($profile, 'company', '');
        $this->data['inn'] = (int) ifset($profile, 'inn', 0);
        $this->data['account_number'] = ifset($profile, 'account_number', '');
        $this->data['mapping_categories'] = ifset($profile, 'mapping', []);
        $this->data['first_update'] = ifset($profile, 'first_update', true);
        $this->data['import_id'] = (int) ifset($profile, 'import_id', 0);

        if (!empty($profile['update_time'])) {
            $this->data['update_time'] = $profile['update_time'];
            $from_date = (new DateTime(date('Y-m-d H:i:s', $profile['update_time'])));
            $interval = DateInterval::createFromDateString(cashTinkoffPlugin::DELTA_LAG_HOURS.' hours');
            $from_date->sub($interval);
        } elseif ($this->data['import_period'] === 'all') {
            $from_date = (new DateTime(date('Y-m-d', strtotime(cashTinkoffPlugin::DEFAULT_START_DATE))));
        } else {
            $from_date = (new DateTime(date('Y-m-d', strtotime($this->data['import_period']))));
        }
        $this->data['from_date'] = $from_date->format('c');
        $this->data['to_date'] = (new DateTime(date('Y-m-d H:i:s', strtotime('tomorrow'))))->format('c');

        $raw_data = $this->getStatementsData(null);
        $this->data['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
        $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
        $this->data['operations'] = ifset($raw_data, 'operations', []);
        $this->plugin()->saveSettings(['current_profile_id' => $this->data['profile_id']]);
        $this->history();
    }

    /**
     * @return cashTinkoffPlugin
     * @throws waException
     */
    private function plugin()
    {
        static $plugin;
        if (!$plugin) {
            /** @var cashTinkoffPlugin $plugin */
            $plugin = wa()->getPlugin('tinkoff');
            $plugin->setCashProfile($this->data['profile_id']);
        }

        return $plugin;
    }

    /**
     * @param $cursor
     * @param $limit
     * @return array
     */
    private function getStatementsData($cursor = '', $limit = self::BATCH_LIMIT)
    {
        try {
            $response = $this->plugin()->getStatement(
                $this->data['tinkoff_id'],
                $this->data['inn'],
                $cursor,
                $this->data['from_date'],
                $this->data['to_date'],
                $limit
            );
            if (ifset($response, 'http_code', 200) !== 200 || !empty($response['error'])) {
                $error = implode(' ', [
                    implode('/', (array) ifset($response, 'errorMessage', [])),
                    implode('/', (array) ifset($response, 'errorDetails', [])),
                    implode('/', (array) ifset($response, 'error_description', []))
                ]);
                $this->data['error'] = $error;
            }
        } catch (Exception $ex) {
            $this->data['error'] = $ex->getMessage();
        }

        return (array) ifempty($response, []);
    }

    /**
     * @return boolean
     * @throws waException
     */
    protected function step()
    {
        $this->plugin()->saveProfile($this->data['profile_id'], ['last_update_time' => time()]);
        if (empty($this->data['operations'])) {
            /** запрашиваем новую порцию (страницу) */
            $raw_data = $this->getStatementsData($this->data['cursor']);
            $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
            $this->data['operations'] = ifset($raw_data, 'operations', []);
        }

        $transactions = $this->plugin()->addTransactionsByAccount($this->data['operations'], $this->data['import_id']);
        $this->data['statements'] = $transactions;
        $this->data['counter'] += count($transactions);
        $this->data['skipped'] += count($this->data['operations']) - count($transactions);

        $old_time = 0;
        foreach ($transactions as $_transaction) {
            $transaction_time = strtotime($_transaction['datetime']);
            if ($old_time < $transaction_time) {
                $old_time = $transaction_time;
            }
        }
        $this->history();
        $this->plugin()->saveProfile($this->data['profile_id'], ['last_update_time' => time()] + (empty($old_time) ? [] : ['update_time' => $old_time]));
        unset($this->data['operations']);

        return true;
    }

    /**
     * @return bool
     */
    protected function isDone()
    {
        return ($this->data['counter'] + $this->data['skipped']) >= $this->data['count_all_statements'];
    }

    /**
     * @param $filename
     * @return bool
     * @throws waException
     */
    protected function finish($filename)
    {
        $this->info();
        $this->correctiveOperation();
        $this->history();
        if (empty($this->data['error'])) {
            $this->plugin()->saveProfile($this->data['profile_id'], [
                'first_update' => false,
                'last_update_time' => time()
            ]);
            $this->writeStorage([]);
        }
        if ($this->getRequest()::post('cleanup')) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws waException
     */
    protected function info()
    {
        $progress = 0;
        if ($this->data['count_all_statements']) {
            $progress = number_format(($this->data['counter'] + $this->data['skipped']) * 100 / $this->data['count_all_statements']);
            if ($this->data['counter'] + $this->data['skipped'] === 0) {
                $progress = number_format(self::BATCH_LIMIT * 100/$this->data['count_all_statements']);
            }
            if ($this->data['first_update']) {
                $data = $this->readStorage();
                $run_data = ifset($data, $this->data['profile_id'], []);
                if (empty($run_data)) {
                    $run_data = ['count_all_statements' => $this->data['count_all_statements'], 'progress' => $progress];
                    $this->writeStorage($run_data);
                }
                $first_progress = (int) ifset($run_data, 'progress', 0);
                if ($first_progress <= (int) $progress) {
                    $this->writeStorage(['progress' => $progress] + $run_data);
                } else {
                    $count_all_statements = (int) ifset($run_data, 'count_all_statements', 0);
                    $progress = $first_progress + intval(($this->data['counter'] + $this->data['skipped']) * 100 / $count_all_statements);
                }
            }
        }
        //$html  = sprintf_wp('Импортировано: %s/%s, пропущено: %s', $this->data['counter'], $this->data['count_all_statements'], $this->data['skipped']);
        $html  = sprintf_wp('Импорт: %s / %s', $this->data['counter'], $this->data['count_all_statements']); // who cares about 'skipped' anyway?
        $html .= ' <div class="spinner custom-ml-4">';
        $this->response([
            'processid'   => $this->processId,
            'ready'       => $this->isDone(),
            'progress'    => $progress,
            'error'       => ifset($this->data, 'error', null),
            'warning'     => ifset($this->data, 'warning', null),
            'text_legend' => $html,
            'cash_account_id' => ifset($this->data, 'cash_account_id', null)
        ]);
    }

    public function restore()
    {
        $steps = ceil($this->data['count_all_statements'] / self::BATCH_LIMIT);
        switch ($steps) {
            case $steps < 5:
                $this->_chunk_time = 1;
                break;
            case $steps < 10:
                $this->_chunk_time = 2;
                break;
            case $steps < 20:
                $this->_chunk_time = 3;
                break;
            case $steps < 40:
                $this->_chunk_time = 4;
                break;
        }
    }

    /**
     * @param $response
     * @return void
     */
    private function response($response = [])
    {
        $this->getResponse()->addHeader('Content-Type', 'application/json');
        $this->getResponse()->sendHeaders();
        echo waUtils::jsonEncode($response);
    }

    /**
     * @return void
     * @throws waException
     */
    private function correctiveOperation()
    {
        if (!$this->data['first_update'] || empty($this->data['account_number'])) {
            return;
        }
        $accounts = $this->plugin()->getAccounts($this->data['tinkoff_id'], $this->data['inn']);
        foreach ($accounts as $_account) {
            if ($this->data['account_number'] == ifset($_account, 'accountNumber', '')) {
                $balance_now = (float) ifset($_account, 'balance', 'balance', 0);
                break;
            }
        }

        if (isset($balance_now)) {
            $transaction_model = cash()->getModel(cashTransaction::class);
            $data_source = $transaction_model
                ->select('SUM(amount) AS sum_amount, MIN(datetime) AS datetime')
                ->where('account_id = ?', $this->data['cash_account_id'])
                ->where('external_source = ?', $this->plugin()->getExternalSource())
                ->where('is_archived = 0')
                ->fetchAssoc();

            $sum_amount = (float) ifset($data_source, 'sum_amount', 0);
            $min_datetime = (new DateTime(ifset($data_source, 'datetime', date('Y-m-d H:i:s'))))->modify('- 1 day');
            $amount = $balance_now - $sum_amount;
            if ($amount) {
                $transaction_model->insert([
                    'date'              => $min_datetime->format('Y-m-d'),
                    'datetime'          => $min_datetime->format('Y-m-d H:i:s'),
                    'account_id'        => $this->data['cash_account_id'],
                    'category_id'       => ($amount > 0 ? -2 : -1),
                    'amount'            => $amount,
                    'description'       => sprintf_wp('Начальный баланс на %s', $min_datetime->format('Y-m-d H:i:s')),
                    'create_contact_id' => wa()->getUser()->getId(),
                    'create_datetime'   => date('Y-m-d H:i:s'),
                    'external_source'   => $this->plugin()->getExternalSource()
                ]);
            }
        }
    }

    /**
     * @return array
     */
    private function readStorage()
    {
        $profile_run_data = (array) $this->getStorage()->read('profile_run_data');

        return (array) ifset($profile_run_data, []);
    }

    /**
     * @param array $data
     * @return void
     */
    private function writeStorage($data)
    {
        $profile_run_data = $this->readStorage();
        $profile_run_data[$this->data['profile_id']] = (array) $data;
        $this->getStorage()->write('profile_run_data', $profile_run_data);
    }

    /**
     * @return void
     * @throws kmwaAssertException
     * @throws waException
     */
    private function history()
    {
        if (empty($this->data['cash_account_id']) || !is_numeric($this->data['cash_account_id'])) {
            return;
        }
        if (empty($this->data['import_id'])) {
            $import_history = cash()->getEntityFactory(cashImport::class)->createNew();
            $import_history->setProvider($this->plugin()->getExternalSource());
            $import_history->setFilename($this->data['company'].' ('.$this->data['account_number'].')');
            cash()->getEntityPersister()->save($import_history);
            $this->data['import_id'] = $import_history->getId();
            $this->plugin()->saveProfile($this->data['profile_id'], ['import_id' => $this->data['import_id']]);
        } else {
            $import_history = cash()->getEntityRepository(cashImport::class)->findById($this->data['import_id']);
        }
        if (!($import_history instanceof cashAbstractEntity)) {
            return null;
        }
        $count_all_added = (int) cash()->getModel(cashTransaction::class)
            ->select('COUNT(id) AS count')
            ->where('import_id = ?', $this->data['import_id'])
            ->where('is_archived = 0')
            ->fetchField('count');
        $import_history->setSuccess($count_all_added);
        $import_history->setFail($this->data['skipped']);
        $import_history->setSettings(json_encode([
            'CLI' => false,
            'counter' => $this->data['counter'],
            'skipped' => $this->data['skipped'],
            'count_all_statements' => $count_all_added,
            'inn' => $this->data['inn'],
            'tinkoff_id' => $this->data['tinkoff_id'],
            'profile_id' => $this->data['profile_id'],
            'account_number' => $this->data['account_number'],
            'cash_account_id' => $this->data['cash_account_id']
        ], JSON_UNESCAPED_UNICODE));
        cash()->getEntityPersister()->update($import_history);
    }

    /**
     * @param $currency_code
     * @return int
     * @throws waException
     */
    private function createAccount($currency_code)
    {
        $currency = 'RUB';
        if (!empty($currency_code)) {
            $system_currencies = (new cashApiSystemGetCurrenciesHandler())->handle(null);
            foreach ($system_currencies as $_system_currency) {
                if (ifset($_system_currency, 'iso4217', '') == $currency_code && isset($_system_currency['code'])) {
                    $currency = $_system_currency['code'];
                    break;
                }
            }
        }

        /** @var cashAccount $account */
        $account = cash()->getEntityFactory(cashAccount::class)->createNew();
        $account->setName(_wp('Т-Бизнес'))
            ->setCurrency($currency)
            ->setDescription('')
            ->setIcon('')
            ->setCustomerContactId(wa()->getUser()->getId());

        cash()->getEntityPersister()->save($account);

        return $account->getId();
    }
}
