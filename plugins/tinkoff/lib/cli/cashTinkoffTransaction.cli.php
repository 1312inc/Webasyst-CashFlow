<?php

/**
 * CRON command example for profile_id = 33:
 * php /var/www/html/cli.php cash tinkoffTransaction 33
 */

class cashTinkoffTransactionCli extends waCliController
{
    private array $info = [];
    private array $profile = [];

    public function setInfo() {
        $this->info = [
            'fail' => 0,
            'loop' => 0,
            'counter' => 0,
            'count_added' => 0,
            'count_all_statements' => 0
        ];
    }

    protected function preExecute()
    {
        $this->setInfo();
    }

    public function execute($profile_id = null)
    {
        $cursor = null;
        $this->info['profile_id'] = ($profile_id ?: waRequest::param(0, 0, waRequest::TYPE_INT));
        if ($this->info['profile_id'] < 1) {
            $this->logFill('Invalid parameter profile_id');
            return null;
        }
        $t_profiles = cashTinkoffPlugin::getProfiles();
        $this->profile = ifset($t_profiles, $this->info['profile_id'], []);
        if (empty($this->profile)) {
            $this->logFill('The profile is missing');
            return null;
        } elseif (!ifempty($this->profile, 'update_time', '')) {
            $this->logFill('The profile is not configured');
            return null;
        }
        $this->plugin($profile_id);
        do {
            try {
                $raw_data = $this->getStatementsData($cursor);
                if (empty($raw_data)) {
                    $this->info['fail']++;
                    continue;
                }
                if (empty($this->info['count_all_statements'])) {
                    $this->info['count_all_statements'] = (int) ifset($raw_data, 'balances', 'operationsCount', 0);
                }
                $cursor = (string) ifset($raw_data, 'nextCursor', null);
                $operations = ifset($raw_data, 'operations', []);
                $this->info['counter'] += count($operations);
                $transactions = $this->plugin()->addTransactionsByAccount($operations);
                $this->info['count_added'] = count($transactions);

                sleep(1);
            } catch (Exception $ex) {
                $this->logFill($ex->getMessage());
                return null;
            }

            $this->info['loop']++;
            if ($this->info['fail'] >= 3) {
                $this->logFill('The error limit has been exceeded');
                exit();
            } elseif ($this->info['loop'] > $this->info['count_all_statements']) {
                $this->logFill('The quantity of operations has been exceeded');
                exit();
            }
        } while ($this->info['counter'] < $this->info['count_all_statements']);

        $this->plugin()->saveProfile($this->info['profile_id'], ['update_time' => time()]);

        $this->logFill('Import OK');
    }

    /**
     * @param $profile_id
     * @return cashTinkoffPlugin
     * @throws waException
     */
    private function plugin($profile_id = null)
    {
        static $plugin;
        if (!$plugin) {
            /** @var cashTinkoffPlugin $plugin */
            $plugin = wa('cash')->getPlugin('tinkoff');
        }
        if ($profile_id) {
            $plugin->setCashProfile($profile_id);
        }

        return $plugin;
    }

    /**
     * @param $cursor
     * @return mixed|null
     */
    private function getStatementsData($cursor = null)
    {
        try {
            $response = $this->plugin()->getStatement(
                ifset($this->profile, 'tinkoff_id', ''),
                ifset($this->profile, 'inn', ''),
                $cursor,
                ifset($this->profile, 'update_time', ''),
                null,
                cashTinkoffPluginBackendRunController::BATCH_LIMIT
            );
            if (ifset($response, 'http_code', 200) !== 200 || !empty($response['error'])) {
                $this->info['fail']++;
                $error = implode(' ', [
                    implode('/', (array) ifset($response, 'error', [])),
                    implode('/', (array) ifset($response, 'errorMessage', [])),
                    implode('/', (array) ifset($response, 'errorDetails', [])),
                    implode('/', (array) ifset($response, 'error_description', []))
                ]);
                $this->logFill($error);
            }
        } catch (Exception $ex) {
            $this->info['fail']++;
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
        waLog::dump(["CLI Tinkoff transaction: $message", $this->info, $this->profile], TINKOFF_FILE_LOG);
    }
}
