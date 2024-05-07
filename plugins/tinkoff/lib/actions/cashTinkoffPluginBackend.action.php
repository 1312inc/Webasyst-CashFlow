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
        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());

        $plugin_settings = $plugin->getSettings();
        $profiles = ifset($plugin_settings, 'profiles', []);
        foreach ($profiles as &$_profile) {
            $_profile['update_date'] = (empty($_profile['update_time']) ? _w('-') : wa_date('humandatetime', $_profile['update_time']));
        }
        $this->view->assign([
            'current_profile_id' => ifset($plugin_settings, 'current_profile_id', key($profiles)),
            'profiles'           => $profiles,
            'expense_operations' => $plugin->getConfigParam('expense'),
            'income_operations'  => $plugin->getConfigParam('income'),
            'categories'         => $categories,
            'cash_accounts'      => $cash_accounts,
            'error'              => waRequest::get('error', '', waRequest::TYPE_STRING_TRIM)
        ]);
    }
}
