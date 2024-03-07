<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $profile_id = waRequest::request('profile_id', null, waRequest::TYPE_STRING_TRIM);

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());
        $plugin_settings = $plugin->getSettings();
        $profiles = ifset($plugin_settings, 'profiles', []);
        $max_profile_id = (int) ifset($plugin_settings, 'max_profile_id', 1);
        if (empty($profiles)) {
            $profile_id = $max_profile_id;
        } elseif (empty($profile_id)) {
            $profile_id = key($profiles);
        } elseif ($profile_id === 'new') {
            $profile_id = ++$max_profile_id;
            $plugin_settings['max_profile_id'] = $max_profile_id;
            $plugin->saveSettings($plugin_settings);
            $this->setTemplate('SettingsProfile.html');
        }

//        $plugin->setCashProfile($profile_id);
//        $plugin->getAccounts();
        $account_numbers = ['Test account number RUB', 'Test account number USD'];

        $this->view->assign([
            'profile_id'      => $profile_id,
            'profiles'        => $this->pasteCronCommand($profiles),
            'operations'      => $plugin->getConfigParam('operations'),
            'categories'      => $categories,
            'cash_accounts'   => $cash_accounts,
            'account_numbers' => $account_numbers
        ]);
    }

    private function pasteCronCommand($profiles)
    {
        if (empty($profiles)) {
            // default profile
            $profiles = [
                1 => ['profile_name' => _wp('Профиль 1')]
            ];
        }
        $root_path = $this->getConfig()->getRootPath();
        foreach ($profiles as $id => $_profile) {
            $profiles[$id]['cron_command'] = "php $root_path/cli.php cash tinkoffTransaction $id";
        }

        return $profiles;
    }
}
