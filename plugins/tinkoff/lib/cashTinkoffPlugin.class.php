<?php

class cashTinkoffPlugin extends cashBusinessPlugin
{
    const LIMIT_STATEMENTS = 5;

    private int $cash_account_id;
    private array $mapping_categories;

    public function __construct($info)
    {
        parent::__construct($info);

        /** обязательно назначаем профиль, через конструктор или setCashProfile() */
        $profile_id = ifempty($info, 'profile_id', 0);
        $this->setCashProfile($profile_id);
    }

    public function setCashProfile($profile_id)
    {
        if (!empty($profile_id)) {
            $settings = $this->getSettings();
            $profile = ifset($settings, 'profiles', $profile_id, []);
        }
        $this->cash_account_id = (int) ifset($profile, 'cash_account', 0);
        $this->mapping_categories = ifset($profile, 'mapping', []);
    }

    private function apiQuery($url, $headers = [], $post_fields = [])
    {
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-4-bank-accounts
     * @return array
     * @throws waException
     */
    public function getAccounts()
    {
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

        $answer = (new waServicesApi())->serviceCall('BANK', [
            'sub_path' => 'get_statement',
            'cursor'   => $cursor,
            'from'     => $from,
            'to'       => $to,
            'balances' => is_null($cursor),
            'limit'    => $limit
        ]);

        return ifset($answer, 'response', 'statement_info', []);
    }

    /**
     * @param $cash_account_id
     * @param $transactions
     * @return array
     * @throws waException
     */
    public function addTransactionsByAccount($cash_account_id, $transactions)
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
            return parent::addTransactionsByAccount($cash_account_id, $transactions);
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
