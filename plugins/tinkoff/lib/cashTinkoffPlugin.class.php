<?php

class cashTinkoffPlugin extends waPlugin
{
    const API_URL = 'https://business.tinkoff.ru/openapi/api/v1/';

    private string $bearer;
    private string $default_account_number;
    private int $cash_account_id;
    private array $mapping_categories;

    public function __construct($info)
    {
        parent::__construct($info);
        $profile = ifempty($info, 'profile', waRequest::request('profile', 1, waRequest::TYPE_INT));
        $settings = $this->getSettings($profile);

        $this->default_account_number = '';
        $this->bearer = ifset($settings, 'access_token', '');
        $this->cash_account_id = (int) ifset($settings, 'cash_account', 0);
        $this->mapping_categories = ifset($settings, 'mapping', []);
    }

    /**
     * @param $endpoint
     * @param $get_params
     * @return array|SimpleXMLElement|string|waNet
     * @throws waException
     */
    public function apiQuery($endpoint = '', $get_params = [])
    {
        $options = [
            'format'         => waNet::FORMAT_JSON,
            'request_format' => waNet::FORMAT_RAW,
            'timeout'        => 60,
            'authorization'  => true,
            'auth_type'      => 'Bearer',
            'auth_key'       => $this->bearer
        ];
        try {
            $net = new waNet($options);
            $response = $net->query(self::API_URL.$endpoint, $get_params);
            if (empty($response)) {
                throw new waException('Empty response');
            }

            $code = intval(ifset($response, 'errorCode', 0));

            if ($code) {
                $message = sprintf(
                    _wp('Ошибка #%d: %s'),
                    $code,
                    ifset($response, 'errorMessage', $code)
                );
                throw new waException($message, $code);
            }
        } catch (waException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new waException($ex->getMessage());
        }

        return $response;
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-company
     * @return mixed|null
     * @throws waException
     * @throws waPaymentException
     */
    public function getCompany()
    {
        $company_info = $this->apiQuery('company');

        return ifempty($company_info, []);
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-4-bank-accounts
     * @return mixed|null
     * @throws waException
     */
    public function getAccounts()
    {
        $accounts = $this->apiQuery('bank-accounts');

        return ifempty($accounts, []);
    }

    /**
     * https://developer.tinkoff.ru/docs/api/get-api-v-1-statement
     * @return mixed|null
     * @throws waException
     */
    public function getStatement()
    {
        $get_params = [
            'operationStatus' => 'Transaction',
            'accountNumber'   => $this->getDefaultAccountNumber(),
            'from'  => date('Y-m-d', strtotime('-1 month')),
            'limit' => 5
        ];
        $operations = $this->apiQuery('statement', $get_params);

        return ifempty($operations, []);
    }

    public function getDefaultAccountNumber()
    {
        if (empty($this->default_account_number)) {
            $accounts = $this->getAccounts();
            foreach ($accounts as $_account) {
                if (ifset($_account, 'accountType', '') == 'Current') {
                    $this->default_account_number = $_account['accountNumber'];
                    break;
                }
            }
        }

        return $this->default_account_number;
    }

    public function addTransactions($transactions)
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
                    'account_id'        => $this->cash_account_id,
                    'category_id'       => (int) ifset($this->mapping_categories, $_transaction['category'], 0),
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
