<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $tinkoff_id = waRequest::get('tinkoff_id', '', waRequest::TYPE_STRING_TRIM);
        $inn = waRequest::get('inn', null, waRequest::TYPE_STRING_TRIM);

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
            $result = $this->createProfiles($tinkoff_id, $inn);
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
     * @param $inn
     * @return array
     * @throws waException
     */
    private function createProfiles($tinkoff_id, $inn)
    {
        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $plugin->saveSettings(['self_mode' => 0]);
        $company = $plugin->getCompany($tinkoff_id, $inn);
        if (!empty($company['errorMessage'])) {
            return ['error' => $company['errorMessage']];
        } elseif (!empty($company['error'])) {
            return ['error' => $company['error']];
        }
        $accounts = $plugin->getAccounts($tinkoff_id, $inn);
        if (!empty($accounts['errorMessage'])) {
            return ['error' => $accounts['errorMessage']];
        }

        $profiles = [];
        $cash_profiles = cashTinkoffPlugin::getProfiles();
        $max_profile_id = (int) $plugin->getSettings('max_profile_id');

        foreach ($accounts as $_account) {
            if (isset($_account['accountNumber'], $_account['name'])) {
                foreach ($cash_profiles as $cash_profile_id => $cash_profile) {
                    if ($cash_profile['account_number'] == $_account['accountNumber']) {
                        break;
                    }
                    unset($cash_profile_id);
                }

                if (empty($cash_profiles) || empty($cash_profile_id)) {
                    $cash_profile_id = ++$max_profile_id;
                }
                $profiles[$cash_profile_id] = [
                    'tinkoff_id' => $tinkoff_id,
                    'inn' => ifset($company, 'requisites', 'inn', ''),
                    'company' => ifset($company, 'name', _wp('Без названия')),
                    'account_number' => $_account['accountNumber'],
                    'account_description' => $_account['name'],
                    'currency_code' => $_account['currency'],
                    'first_update' => true,
                    'update_timeout' => cashTinkoffPlugin::DEFAULT_UPDATE_TIMEOUT,
                    'status' => 'ok',
                    'status_description' => ''
                ];
                if (empty($tinkoff_id)) {
                    $profiles[$cash_profile_id]['status_description'] = _wp('Отсутствует Tinkoff ID');
                    $profiles[$cash_profile_id]['status'] = 'danger';
                } elseif (empty($profiles[$cash_profile_id]['inn'])) {
                    $profiles[$cash_profile_id]['status_description'] = _wp('Отсутствует ИНН компании');
                    $profiles[$cash_profile_id]['status'] = 'danger';
                }
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
