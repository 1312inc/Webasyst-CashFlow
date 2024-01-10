<?php

class cashTinkoffTransactionCli extends waCliController
{
   private array $info = [];

    protected function preExecute()
    {
        $this->info = [
            'counter' => 0,
            'count_all_statements' => 0
        ];
    }

    public function execute()
    {
        $cursor = '';
        $this->info['profile_id'] = waRequest::param(0, 0, waRequest::TYPE_INT);
        if ($this->info['profile_id'] < 1) {
            $this->logFill('Invalid parameter profile_id');
            return null;
        }

        do {
            try {
                $raw_data = $this->getStatementsData($cursor);
                if (empty($this->info['count_all_statements'])) {
                    $this->info['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
                }
                $cursor = (string) ifset($raw_data, 'nextCursor', '');
                $operations = ifset($raw_data, 'operations', []);
                $transactions = $this->plugin()->addTransactions($operations);
                $this->info['counter'] += count($transactions);

                sleep(1);
            } catch (Exception $ex) {
                $this->logFill($ex->getMessage());
                return null;
            }
        } while ($this->info['counter'] < $this->info['count_all_statements']);

        $this->logFill('Import OK');
    }

    /**
     * @return cashTinkoffPlugin
     */
    private function plugin()
    {
        static $plugin;
        if (!$plugin) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $this->info['profile_id']]);
        }

        return $plugin;
    }

    /**
     * @param $from
     * @param $to
     * @param $cursor
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
                $this->logFill($error);
            }
        } catch (Exception $ex) {
            $this->logFill($ex->getMessage());
        }

        return ifset($response, []);
    }

    /**
     * @param string $message
     * @return void
     */
    private function logFill(string $message = '')
    {
        waLog::log([$message, $this->info], cashTinkoffPlugin::FILE_LOG);
    }
}
