<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $tinkoff_id = waRequest::get('tinkoff_id', '', waRequest::TYPE_STRING_TRIM);

        $backend_url = rtrim(wa()->getRootUrl(true), DIRECTORY_SEPARATOR).wa()->getAppUrl();
        if (empty($tinkoff_id)) {
            /** no auth */
            wa()->getStorage()->del('cash.tinkoff_back_redirect');
            wa()->getStorage()->set('cash.tinkoff_back_redirect', $backend_url.'?plugin=tinkoff');
            $answer = (new waServicesApi())->serviceCall('BANK', [
                'sub_path' => 'get_userinfo',
                'return_uri' => $backend_url.'?plugin=tinkoff&module=auth'
            ]);
            $status = ifset($answer, 'status', 200);
            $response = ifset($answer, 'response', []);

            if ($status !== 200 || ifset($response, 'http_code', 200) !== 200) {
                // error
                $this->setError(ifset($response, 'error_description', 'Что-то пошло не так'));
                waLog::log($answer,TINKOFF_FILE_LOG);
                return null;
            }

            $this->response = $response;
        } else {
            $result = $this->createProfiles($tinkoff_id);
            $redirect_url = wa()->getStorage()->get('cash.tinkoff_back_redirect');
            if ($error = ifempty($result, 'error', null)) {
                waLog::log($error,TINKOFF_FILE_LOG);
                $redirect_url .= "&error=$error";
            }

            if ($redirect_url) {
                $this->redirect($redirect_url);
            }
            $this->redirect($backend_url);
        }
    }

    /**
     * @param $tinkoff_id
     * @return array
     * @throws waException
     */
    private function createProfiles($tinkoff_id)
    {
        $profiles = [];
        $now = date('Y-m-d H:i:s');

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $max_profile_id = (int) $plugin->getSettings('max_profile_id');
        $company = $plugin->getCompany($tinkoff_id);
        if (!empty($company['errorMessage'])) {
            return ['error' => $company['errorMessage']];
        } elseif (!empty($company['error'])) {
            return ['error' => $company['error']];
        }
        $accounts = $plugin->getAccounts($tinkoff_id);
        if (!empty($accounts['errorMessage'])) {
            return ['error' => $accounts['errorMessage']];
        }
        foreach ($accounts as $_account) {
            if (isset($_account['accountNumber'], $_account['name'])) {
                $max_profile_id++;
                $profiles[$max_profile_id] = [
                    'tinkoff_id' => $tinkoff_id,
                    'inn' => ifset($company, 'requisites', 'inn', ''),
                    'company' => ifset($company, 'name', _wp('Без названия')),
                    'account_number' => ifset($_account, 'accountNumber', ''),
                    'account_description' => $_account['name'],
                    'last_connect_date' => $now
                ];
            }
        }
        $plugin->saveSettings(['max_profile_id' => $max_profile_id]);
        $edit_controller = new cashTinkoffPluginProfileEditController();
        foreach ($profiles as $profile_id => $test_profile) {
            $edit_controller->execute($profile_id, $profiles);
        }

        return $profiles;
    }
}
