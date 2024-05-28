<?php

class cashTinkoffPluginConnectController extends waJsonController
{
    public function execute()
    {
        $tinkoff_token = waRequest::post('token', '', waRequest::TYPE_STRING_TRIM);

        if (empty($tinkoff_token)) {
            $this->setError(ifset($response, 'error_description', _wp('Токен не может быть пустым')));
            waLog::log('Empty token, self mode', TINKOFF_FILE_LOG);
        }

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $plugin->saveSettings(['tinkoff_token' => $tinkoff_token]);

        $company = $plugin->getCompany();
        if (empty($company)) {
            $_err = _wp('Информация о компании не получена');
            $this->setError($_err);
            waLog::log($_err, TINKOFF_FILE_LOG);
            return null;
        } elseif (!empty($company['errorMessage'])) {
            $this->setError($company['errorMessage']);
            waLog::log($company, TINKOFF_FILE_LOG);
            return null;
        } elseif (!empty($company['error'])) {
            $this->setError($company['error']);
            waLog::log($company, TINKOFF_FILE_LOG);
            return null;
        }
        $accounts = $plugin->getAccounts();
        if (empty($accounts)) {
            $_err = _wp('Информация о счетах не получена');
            $this->setError($_err);
            waLog::log($_err, TINKOFF_FILE_LOG);
            return null;
        } elseif (!empty($accounts['errorMessage'])) {
            $this->setError($accounts['errorMessage']);
            waLog::log($accounts, TINKOFF_FILE_LOG);
            return null;
        }

        $profiles = [];
        $cash_profiles = cashTinkoffPlugin::getProfiles();
        $max_profile_id = (int)$plugin->getSettings('max_profile_id');

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
                    'tinkoff_id' => '',
                    'inn' => ifset($company, 'requisites', 'inn', ''),
                    'company' => ifset($company, 'name', _wp('Без названия')),
                    'account_number' => $_account['accountNumber'],
                    'account_description' => $_account['name'],
                    'first_update' => true,
                    'update_timeout' => cashTinkoffPlugin::DEFAULT_UPDATE_TIMEOUT,
                    'status' => 'ok',
                    'status_description' => ''
                ];
            }
        }
        $plugin->saveSettings(['max_profile_id' => $max_profile_id, 'self_mode' => 1]);
        $edit_controller = new cashTinkoffPluginProfileEditController();
        foreach ($profiles as $profile_id => $test_profile) {
            $edit_controller->execute($profile_id, $profiles);
        }
    }
}
