<?php

class cashTinkoffPluginBackendRunController extends waLongActionController
{
    const BATCH_LIMIT = 50;

    /**
     * @return void
     * @throws waException
     */
    protected function init()
    {
        $this->data = [
            'profile_id'    => waRequest::post('profile_id', 0, waRequest::TYPE_INT),
            'import_period' => waRequest::post('import_period', 'all', waRequest::TYPE_STRING_TRIM),
            'error'         => null,
            'warning'       => null,
            'statements'    => [],
            'counter'       => 0,
            'skipped'       => 0,
            'cursor'        => '',
            'count_all_statements' => 0,
        ];

        if (empty($this->data['profile_id'])) {
            $this->data['error'] = sprintf_wp('Параметр %s обязательный', 'profile_id');
            return;
        }

        $profile = $this->plugin()->getProfile($this->data['profile_id']);
        if (!ifset($profile, 'cash_account', 0)) {
            $this->data['error'] = _wp('Не настроен счет импорта');
            return;
        }
        $this->data['cash_account_id'] = (int) ifset($profile, 'cash_account', 0);
        $this->data['tinkoff_id'] = (string) ifset($profile, 'tinkoff_id', '');
        $this->data['inn'] = (int) ifset($profile, 'inn', 0);
        $this->data['account_number'] = ifset($profile, 'account_number', '');
        $this->data['mapping_categories'] = ifset($profile, 'mapping', []);

        if ($this->data['import_period'] === 'all') {
            $from_date = (new DateTime(date('Y-m-d', strtotime(cashTinkoffPlugin::DEFAULT_START_DATE))))->format('c');
        } else {
            $from_date = (new DateTime(date('Y-m-d', strtotime($this->data['import_period']))))->format('c');
        }
        $this->data['from_date'] = $from_date;
        $this->data['to_date'] = (new DateTime(date('Y-m-d', strtotime('now'))))->format('c');

        $raw_data = $this->getStatementsData(null);
        $this->data['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
        $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
        $this->data['operations'] = ifset($raw_data, 'operations', []);
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
        if (empty($this->data['operations'])) {
            /** запрашиваем новую порцию (страницу) */
            $raw_data = $this->getStatementsData($this->data['cursor']);
            $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
            $this->data['operations'] = ifset($raw_data, 'operations', []);
            return false;
        }
waLog::dump(['STEP-1', 'operations' => $this->data['operations']], TINKOFF_FILE_LOG);

        $transactions = $this->plugin()->addTransactionsByAccount($this->data['operations']);
waLog::dump(['STEP-2', '$transactions' => $transactions], TINKOFF_FILE_LOG);
        $this->data['statements'] = $transactions;
        $this->data['counter'] += count($transactions);
        $this->data['skipped'] += count($this->data['operations']) - count($transactions);
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
        if (empty($this->data['error'])) {
            $this->plugin()->saveProfile($this->data['profile_id'], ['update_date' => date('Y-m-d H:i:s')]);
            $this->plugin()->saveSettings(['current_profile_id' => $this->data['profile_id']]);
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
            $progress = ($this->data['counter'] + $this->data['skipped']) * 100 / $this->data['count_all_statements'];
        }

        $html  = sprintf_wp('Импортировано: %s/%s, пропущено: %s', $this->data['counter'], $this->data['count_all_statements'], $this->data['skipped']);
        $html .= ' <div class="spinner custom-ml-4">';
        $this->response([
            'processid'   => $this->processId,
            'ready'       => $this->isDone(),
            'progress'    => number_format($progress),
            'error'       => ifset($this->data, 'error', null),
            'warning'     => ifset($this->data, 'warning', null),
            'text_legend' => $html
        ]);
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
        if (empty($this->data['account_number']) || $this->data['import_period'] !== 'all') {
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
                ->where('external_source = s:source', ['source' => $this->plugin()->getExternalSource()])
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
}
