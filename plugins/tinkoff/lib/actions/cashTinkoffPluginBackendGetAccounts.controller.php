<?php

class cashTinkoffPluginBackendGetAccountsController extends waJsonController
{
    public function execute()
    {
        $response = [];
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ($profile_id > 0) {
            try {
                /** @var cashTinkoffPlugin $plugin */
                $plugin = wa()->getPlugin('tinkoff');
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
                waLog::log($ex->getMessage(),TINKOFF_FILE_LOG);
            }
        }

        $accounts = [];
        foreach ($response as $_account) {
            $accounts[] = [
                'name'    => ifset($_account, 'name', ''),
                'number'  => ifset($_account, 'accountNumber', ''),
                'default' => (ifset($_account, 'accountType', '') == 'Current')
            ];
        }

        $this->response = $accounts;
    }
}
