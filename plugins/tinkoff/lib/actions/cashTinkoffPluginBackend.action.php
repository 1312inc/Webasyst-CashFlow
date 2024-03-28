<?php

class cashTinkoffPluginBackendAction extends waViewAction
{
    protected function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        if (!cash()->getUser()->isAdmin()) {
            throw new kmwaForbiddenException();
        }
    }

    public function execute()
    {
        $account_numbers = [];

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());
        $accounts = $plugin->getAccounts();
        if (empty($accounts['error'])) {
            foreach ($accounts as $_account) {
                if (isset($_account['accountNumber'], $_account['name'])) {
                    $account_numbers[$_account['accountNumber']] = $_account['name'].($_account['accountNumber'] ? ' ('.$_account['accountNumber'].')' : '');
                }
            }
        } else {
            $account_numbers = $accounts;
        }

        $profiles = cashTinkoffPlugin::getProfiles();
        $this->view->assign([
            'profile_id'      => key($profiles),
            'profile'         => ifset($profiles, key($profiles), []),
            'profiles'        => $profiles,
            'operations'      => $plugin->getConfigParam('operations'),
            'categories'      => $categories,
            'cash_accounts'   => $cash_accounts,
            'account_numbers' => $account_numbers
        ]);
    }
}
