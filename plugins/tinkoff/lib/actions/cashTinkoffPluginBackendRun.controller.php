<?php

class cashTinkoffPluginBackendRunController extends waLongActionController
{
    const BATCH_LIMIT = 5;

    /**
     * @return void
     * @throws waException
     */
    protected function init()
    {
        $this->data = [
            'profile_id' => waRequest::post('profile_id', 0, waRequest::TYPE_INT),
            'error'      => null,
            'warning'    => null,
            'statements' => [],
            'counter'    => 0,
            'cursor'     => '',
            'count_all_statements' => 0,
        ];

        if (empty($this->data['profile_id'])) {
            $this->data['error'] = sprintf_wp('Параметр %s обязательный', 'profile_id');
            return;
        }

        $plugin = wa('cash')->getPlugin('tinkoff');
        $settings = $plugin->getSettings();
        $profile = ifset($settings, 'profiles', $this->data['profile_id'], []);
        $this->data['cash_account_id'] = (int) ifset($profile, 'cash_account', 0);
        $this->data['mapping_categories'] = ifset($profile, 'mapping', []);

        $raw_data = $this->getStatementsData(null);
        $this->data['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
        $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
        $this->data['operations'] = ifset($raw_data, 'operations', []);
    }

    /**
     * @param $cursor
     * @param $from
     * @param $to
     * @return array
     */
    private function getStatementsData($cursor = '', $from = null, $to = null)
    {
        static $services_api;
        if (!$services_api) {
            $services_api = new waServicesApi();
        }
        try {
            $answer = $services_api->serviceCall('BANK', [
                'sub_path' => 'get_statement',
                'cursor'   => $cursor,
                'from'     => $from,
                'to'       => $to,
                'balances' => is_null($cursor),
                'limit'    => self::BATCH_LIMIT
            ]);
            $status = ifset($answer, 'status', 200);
            $response = ifset($answer, 'response', 'statement_info', []);
            if (
                $status !== 200
                || ifset($response, 'http_code', 200) !== 200
                || !empty($response['error'])
            ) {
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

        return ifempty($response, []);
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

        $transactions = $this->addTransactions($this->data['operations']);
        $this->data['statements'] = $transactions;
        $this->data['counter'] += count($transactions);
        unset($this->data['operations']);

        return true;
    }

    /**
     * @return bool
     */
    protected function isDone()
    {
        return $this->data['counter'] >= $this->data['count_all_statements'];
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function finish($filename)
    {
        $this->info();
        if ($this->getRequest()::post('cleanup')) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     */
    protected function info()
    {
        $progress = 0;
        if ($this->data['count_all_statements']) {
            $progress = $this->data['counter'] * 100 / $this->data['count_all_statements'];
        }

        $this->response([
            'processid'  => $this->processId,
            'ready'      => $this->isDone(),
            'progress'   => number_format($progress, 2),
            'error'      => ifset($this->data, 'error', null),
            'warning'    => ifset($this->data, 'warning', null),
            'statements' => ifset($this->data, 'statements', []),
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
     * @param $transactions
     * @return mixed
     * @throws waException
     */
    private function addTransactions($transactions)
    {
        if (!empty($transactions)) {
            $transaction_model = cash()->getModel(cashTransaction::class);
            $create_contact_id = wa()->getUser()->getId();
            foreach ($transactions as &$_transaction) {
                $now = date('Y-m-d H:i:s');
                $is_credit = ('Credit' == ifset($_transaction, 'typeOfOperation', 'Credit'));
                $date_operation = strtotime(ifset($_transaction, 'operationDate', $now));
                $_transaction = [
                    'date'              => date('Y-m-d', $date_operation),
                    'datetime'          => date('Y-m-d H:i:s', $date_operation),
                    'account_id'        => $this->data['cash_account_id'],
                    'category_id'       => (int) ifset($this->data['mapping_categories'], $_transaction['category'], 0),
                    'amount'            => ($is_credit ? 1 : -1) * ifset($_transaction, 'operationAmount', 0),
                    'description'       => ifset($_transaction, 'payPurpose', ''),
                    'create_contact_id' => $create_contact_id,
                    'create_datetime'   => $now,
                    'external_source'   => 'api_tinkoff',
                    'external_hash'     => ifset($_transaction, 'operationId', null)
                ];
            }
            $transaction_model->multipleInsert($transactions);
        }

        return $transactions;
    }
}
