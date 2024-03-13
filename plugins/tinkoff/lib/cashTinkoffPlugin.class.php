<?php

class cashTinkoffPlugin extends cashBusinessPlugin
{
    const LIMIT_STATEMENTS = 5000;
    const DEFAULT_START_DATE = '2006-01-01 00:00:00';
    const API_URL = 'https://business.tinkoff.ru/openapi/api/';
    const USER_INFO_URL = 'https://id.tinkoff.ru/userinfo/userinfo';

    private bool $self_mode;
    private string $tinkoff_token;
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

    public function setCashProfile($profile_id)
    {
        if (!empty($profile_id)) {
            $profile = $this->getProfiles($profile_id);
        }
        $this->cash_account_id = (int) ifset($profile, 'cash_account', 0);
        $this->mapping_categories = ifset($profile, 'mapping', []);
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
        if ($this->self_mode) {
            return $this->apiQuery(self::API_URL.'v4/bank-accounts');
        }
        $answer = (new waServicesApi())->serviceCall('BANK', ['sub_path' => 'get_accounts']);

        return ifset($answer, 'response', 'accounts_info', []);
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-company
     * @return array
     * @throws waException
     */
    public function getCompany()
    {
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
        if ($this->self_mode) {
            $default_account_number = '';
            $accounts = $this->getAccounts();
            if ($accounts['http_code'] === 200) {
                foreach ($accounts as $_account) {
                    if (ifset($_account, 'accountType', '') == 'Current') {
                        $default_account_number = $_account['accountNumber'];
                        break;
                    }
                }
            }
            $get_params += [
                'operationStatus' => 'Transaction',
                'accountNumber'   => $default_account_number,
                'withBalances'    => is_null($cursor)
            ];
            return $this->apiQuery(self::API_URL.'v1/statement?'.http_build_query($get_params));
        }

        $answer = (new waServicesApi())->serviceCall('BANK', $get_params + [
            'sub_path' => 'get_statement',
            'balances' => is_null($cursor)
        ]);

        return ifset($answer, 'response', 'statement_info', []);
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
    public function getProfiles($profile_id = 0)
    {
        $profiles = (array) $this->getSettings('profiles');
        if ((int) $profile_id > 0) {
            return (array) ifset($profiles, $profile_id, []);
        }

        return $profiles;
    }

    /**
     * @param $profile_id
     * @param $profile
     * @return bool
     */
    public function saveProfiles($profile_id, $profile = [])
    {
        $profiles = (array) $this->getSettings('profiles');
        if (empty($profile) || $profile_id < 1) {
            return false;
        }
        $profiles[$profile_id] = $profile + ifset($profiles, $profile_id, []);
        try {
            self::saveSettings(['profiles' => $profiles]);
        } catch (Exception $e) {
            waLog::log($e->getMessage(),TINKOFF_FILE_LOG);
            return false;
        }

        return true;
    }
}
