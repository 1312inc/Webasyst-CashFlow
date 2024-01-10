<?php

class cashTinkoffPluginBackendRunController extends waLongActionController
{
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
        $raw_data = $this->getStatementsData();
        $this->data['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
        $this->data['cursor'] = (string) ifset($raw_data, 'nextCursor', '');
        $this->data['operations'] = ifset($raw_data, 'operations', []);
    }

    /**
     * @return cashTinkoffPlugin
     */
    protected function plugin()
    {
        static $plugin;
        if (!$plugin) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $this->data['profile_id']]);
        }

        return $plugin;
    }

    /**
     * @param $cursor
     * @param $from
     * @param $to
     * @return array
     */
    private function getStatementsData($cursor = '', $from = null, $to = null)
    {
        try {
            $response = $this->plugin()->getStatement($cursor, $from, $to);
            if (ifset($response, 'http_code', 200) !== 200) {
                $error = implode(' ', [
                    ifset($response, 'errorMessage', ''),
                    ifset($response, 'errorDetails', ''),
                    ifset($response, 'error_description', '')
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

        $transactions = $this->plugin()->addTransactions($this->data['operations']);
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
}
