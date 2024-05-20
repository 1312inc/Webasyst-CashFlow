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
        $profile_run_data = $this->getStorage()->read('profile_run_data');
        $current_profile_id = ifset($plugin_settings, 'current_profile_id', key($profiles));
        foreach ($profiles as $_profile_id => &$_profile) {
            $_profile['update_date'] = (empty($_profile['last_update_time']) ? '' : wa_date('humandatetime', $_profile['last_update_time']));
            /** данные для продолжения прогресса импорта */
            if (!empty($profile_run_data[$_profile_id])) {
                $_profile['run_data'] = [
                    'progress' => (int) ifempty($profile_run_data, $_profile_id, 'progress', 0)
                ];
            }
            if ($current_profile_id == $_profile_id) {
                $imports = cash()->getEntityRepository(cashImport::class)->findLastN(10, ['api_tinkoff_'.ifempty($_profile, 'cash_account', '')]);
                $_profile['imports'] = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
            }
        }

        $this->view->assign([
            'current_profile_id' => $current_profile_id,
            'profiles'           => $profiles,
            'expense_operations' => $plugin->getConfigParam('expense'),
            'income_operations'  => $plugin->getConfigParam('income'),
            'categories'         => $categories,
            'cash_accounts'      => $cash_accounts,
            'error'              => waRequest::get('error', '', waRequest::TYPE_STRING_TRIM)
        ]);
    }
}
