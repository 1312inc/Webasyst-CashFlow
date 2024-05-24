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
        $plugin->saveSettings(['tinkoff_token' => $tinkoff_token, 'self_mode' => 1]);

        $company = $plugin->getCompany();
        if (!empty($company['errorMessage'])) {
            $this->setError($company['errorMessage']);
            return null;
        } elseif (!empty($company['error'])) {
            $this->setError($company['error']);
            return null;
        }
        $accounts = $plugin->getAccounts();
        if (!empty($accounts['errorMessage'])) {
            $this->setError($accounts['errorMessage']);
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
        $plugin->saveSettings(['max_profile_id' => $max_profile_id]);
        $edit_controller = new cashTinkoffPluginProfileEditController();
        foreach ($profiles as $profile_id => $test_profile) {
            $edit_controller->execute($profile_id, $profiles);
        }
    }
}
