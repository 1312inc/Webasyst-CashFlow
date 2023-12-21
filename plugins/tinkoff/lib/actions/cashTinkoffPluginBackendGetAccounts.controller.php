<?php

class cashTinkoffPluginBackendGetAccountsController extends waJsonController
{
    public function execute()
    {
        $response = [];
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            try {
                $response = $plugin->getAccounts();
                if (ifset($response, 'http_code', 200) !== 200) {
                    $error = implode(' ', [
                        ifset($response, 'errorMessage', ''),
                        ifset($response, 'errorDetails', ''),
                        ifset($response, 'error_description', '')
                    ]);
                    $this->setError($error);
                }
                unset($response['http_code']);
            } catch (Exception $ex) {
                $this->setError($ex->getMessage());
            }
        }

        $accounts = [];
        foreach ($response as $_account) {
            $accounts[] = [
                'name'    => ifset($_account, 'name', ''),
                'default' => (ifset($_account, 'accountType', '') == 'Current')
            ];
        }

        $this->response = $accounts;
    }
}
