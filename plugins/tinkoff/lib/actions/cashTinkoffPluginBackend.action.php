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
        $count_sources = [];
        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());

        $plugin_settings = $plugin->getSettings();
        $profiles = ifset($plugin_settings, 'profiles', []);
        $profile_run_data = $this->getStorage()->read('profile_run_data');
        if (!empty($profile_run_data)) {
            $count_sources = cash()->getModel(cashTransaction::class)->query("
            SELECT external_source, COUNT(external_source) AS count_source  FROM cash_transaction ct 
            WHERE external_source IS NOT NULL GROUP BY external_source
        ")->fetchAll('external_source');
        }
        foreach ($profiles as $_profile_id => &$_profile) {
            $api_source = 'api_tinkoff_'.ifset($_profile, 'cash_account', '');
            $_profile['update_date'] = (empty($_profile['update_time']) ? '' : wa_date('humandatetime', $_profile['update_time']));
            if (array_key_exists($api_source, $count_sources)) {
                /** данные для продолжения прогресса импорта */
                $count_all_statements = (int) ifempty($profile_run_data, $_profile_id, 'count_all_statements', 0);
                if ($count_all_statements > 0) {
                    $counter = ifempty($count_sources, $api_source, 'count_source', 0);
                    $_profile['run_data'] = [
                        'counter' => $counter,
                        'count_all_statements' => $count_all_statements,
                        'progress' => number_format($counter*100/$count_all_statements)
                    ];
                }
            }
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
