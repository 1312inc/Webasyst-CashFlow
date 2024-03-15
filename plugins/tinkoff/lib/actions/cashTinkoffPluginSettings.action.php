<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $account_numbers = [];

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());
        $accounts = $plugin->getAccounts();
        foreach ($accounts as $_account) {
            if (isset($_account['accountNumber'], $_account['name'])) {
                $account_numbers[$_account['accountNumber']] = $_account['name'].($_account['accountNumber'] ? ' ('.$_account['accountNumber'].')' : '');
            }
        }

        $profiles = cashTinkoffPlugin::getProfiles();

        $this->view->assign([
            'profile_id'      => key($profiles),
            'profiles'        => $profiles,
            'operations'      => $plugin->getConfigParam('operations'),
            'categories'      => $categories,
            'cash_accounts'   => $cash_accounts,
            'account_numbers' => $account_numbers
        ]);
    }
}
