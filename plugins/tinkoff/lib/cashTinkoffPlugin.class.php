<?php

class cashTinkoffPlugin extends cashBusinessPlugin
{
    const LIMIT_STATEMENTS = 5000;
    const DEFAULT_START_DATE = '2006-01-01 00:00:00';
    const API_URL = 'https://business.tinkoff.ru/openapi/api/';
    const USER_INFO_URL = 'https://id.tinkoff.ru/userinfo/userinfo';

    private bool $self_mode;
    private string $profile_id;
    private string $tinkoff_token;
    private string $account_number;
    private array $mapping_categories;

    public function __construct($info)
    {
        parent::__construct($info);

        /** для Self-сценария */
        $this->self_mode = !!$this->getSettings('self_mode');
        $this->tinkoff_token = (string) $this->getSettings('tinkoff_token');

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

    private function apiQuery($url, $headers = [], $post_fields = [])
    {
        $options = [
            'format'         => waNet::FORMAT_JSON,
            'request_format' => waNet::FORMAT_RAW,
            'timeout'        => 60
        ];
        $headers += [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer '.$this->tinkoff_token
        ];
        try {
            $net = new waNet($options, $headers);
            try {
                $method = (empty($post_fields) ? waNet::METHOD_GET : waNet::METHOD_POST);
                $response = $net->query($url, http_build_query($post_fields), $method);
            } catch (Exception $ex) {
                $response = $net->getResponse();
            }
            $response += ['http_code' => $net->getResponseHeader('http_code')];
        } catch (Exception $exception) {
            waLog::log($exception->getMessage(), TINKOFF_FILE_LOG);
            throw new waException($exception->getMessage());
        }

        return $response;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-4-bank-accounts
     * @return array
     * @throws waException
     */
    public function getAccounts()
    {
        $cache = new waVarExportCache('accounts', 60, 'cash/plugins/tinkoff');
        if ($accounts = $cache->get()) {
            return $accounts;
        }
        $this->saveProfile($this->profile_id, ['last_connect_date' => date('Y-m-d H:i:s')]);
        if ($this->self_mode) {
            $result = $this->apiQuery(self::API_URL.'v4/bank-accounts');
        } else {
            $answer = (new waServicesApi())->serviceCall('BANK', ['sub_path' => 'get_accounts']);
            $result = ifset($answer, 'response', 'accounts_info', []);
        }
        $cache->set($result);

        return $result;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-company
     * @return array
     * @throws waException
     */
    public function getCompany()
    {
        $this->saveProfile($this->profile_id, ['last_connect_date' => date('Y-m-d H:i:s')]);
        if ($this->self_mode) {
            return $this->apiQuery(self::API_URL.'v1/company');
        }
        $answer = (new waServicesApi())->serviceCall('BANK', ['sub_path' => 'get_company']);

        return ifset($answer, 'response', 'company_info', []);
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-statement
     * @param $cursor
     * @param $from
     * @param $to
     * @param $limit
     * @return array
     * @throws waException
     */
    public function getStatement($cursor, $from, $to, $limit = self::LIMIT_STATEMENTS)
    {
        $get_params = [
            'cursor' => $cursor,
            'from'   => $from,
            'to'     => $to,
            'limit'  => $limit
        ];
        $this->saveProfile($this->profile_id, ['last_connect_date' => date('Y-m-d H:i:s')]);
        if ($this->self_mode) {
            $get_params += [
                'operationStatus' => 'Transaction',
                'accountNumber'   => $this->account_number,
                'withBalances'    => is_null($cursor)
            ];
            return $this->apiQuery(self::API_URL.'v1/statement?'.http_build_query($get_params));
        }

        $answer = (new waServicesApi())->serviceCall('BANK', $get_params + [
            'sub_path'       => 'get_statement',
            'account_number' => $this->account_number,
            'balances'       => is_null($cursor)
        ]);

        return ifset($answer, 'response', 'statement_info', []);
    }

    public function getCircleIcon()
    {
        return '/wa-apps/cash/plugins/tinkoff/img/tinkoff_circle.svg';
    }

    /**
     * @param $transactions
     * @return array
     * @throws waException
     */
    public function addTransactionsByAccount($transactions)
    {
        if (!empty($transactions)) {
            foreach ($transactions as &$_transaction) {
                $is_credit = ('Credit' == ifset($_transaction, 'typeOfOperation', 'Credit'));
                $_transaction = [
                    'date_operation' => ifset($_transaction, 'operationDate', date('Y-m-d H:i:s')),
                    'category_id'    => (int) ifset($this->mapping_categories, $_transaction['category'], 0),
                    'amount'         => ($is_credit ? 1 : -1) * ifset($_transaction, 'operationAmount', 0),
                    'description'    => ifset($_transaction, 'payPurpose', ''),
                    'hash'           => ifset($_transaction, 'operationId', null)
                ];
            }
            return parent::addTransactionsByAccount($transactions);
        }

        return [];
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
            waLog::log($e->getMessage(),TINKOFF_FILE_LOG);
            return false;
        }

        return true;
    }

    public static function getProfiles()
    {
        return (array) wa()->getPlugin('tinkoff')->getSettings('profiles');
    }
}
