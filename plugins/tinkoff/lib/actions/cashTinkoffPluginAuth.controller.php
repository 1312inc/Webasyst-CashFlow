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
        } else {
            $redirect_url = wa()->getStorage()->get('cash.tinkoff_back_redirect');
            if ($redirect_url) {
                $this->redirect($redirect_url);
            }
            $this->redirect($backend_url);
        }
        $status = ifset($answer, 'status', 200);
        $response = ifset($answer, 'response', []);
        if (
            $status !== 200
            || ifset($response, 'http_code', 200) !== 200
        ) {
            // error
            $this->setError('Что-то пошло не так');
            waLog::log($answer,TINKOFF_FILE_LOG);
            return null;
        }
        $tinkoff_id = ifset($response, 'tinkoff_id', $tinkoff_id);

        $this->response = $response + ['profiles' => $this->createProfiles($tinkoff_id)];
    }

    private function createProfiles($tinkoff_id)
    {
        $profiles = [];
        $now = date('Y-m-d H:i:s');

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $max_id = (int) $plugin->getSettings('max_id');
        $company = $plugin->getCompany();
        $accounts = $plugin->getAccounts();
        if (empty($accounts['error'])) {
            foreach ($accounts as $_account) {
                if (isset($_account['accountNumber'], $_account['name'])) {
                    $max_id++;
                    $profiles[$max_id] = [
                        'company' => ifset($company, 'name', _wp('Без названия')),
                        'account_number' => ifset($_account, 'accountNumber', ''),
                        'account_description' => $_account['name'],
                        'tinkoff_id' => $tinkoff_id,
                        'last_connect_date' => $now
                    ];
                }
            }
            $plugin->saveSettings(['max_id' => $max_id]);
        }

        $edit_controller = new cashTinkoffPluginProfileEditController();
        foreach ($profiles as $profile_id => $test_profile) {
            $edit_controller->execute($profile_id, $profiles);
        }

        return $profiles;
    }
}
