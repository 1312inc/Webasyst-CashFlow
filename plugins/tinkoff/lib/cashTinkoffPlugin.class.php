<?php

class cashTinkoffPlugin extends waPlugin
{
    const API_URL = 'https://business.tinkoff.ru/openapi/api/v1/';

    private $bearer;
    private $default_account_number;

    public function __construct($info)
    {
        parent::__construct($info);
        $profile = ifempty($info, 'profile', waRequest::request('profile', 1, waRequest::TYPE_INT));
        $settings = $this->getSettings($profile);
        $this->bearer = ifset($settings, 'access_token', '');
        $this->default_account_number = '';
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

    /**
     * @param $settings
     * @return void
     * @throws waException
     */
    public function saveSettings($settings = [])
    {
        $profile = waRequest::post('profile', 1, waRequest::TYPE_INT);

        parent::saveSettings([$profile => $settings]);
    }

    /**
     * @param $profile
     * @return mixed|null
     */
    public function getSettings($profile = null)
    {
        return parent::getSettings($profile);
    }
}
