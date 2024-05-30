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
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContactWithCounter(wa()->getUser());

        $plugin_settings = $plugin->getSettings();
        $profiles = ifset($plugin_settings, 'profiles', []);
        $profile_run_data = $this->getStorage()->read('profile_run_data');
        foreach ($profiles as $_profile_id => &$_profile) {
            /** проверяем аккаунты */
            $cash_account_id = (int) ifset($_profile, 'cash_account', 0);
            if ($cash_account_id && !array_key_exists($cash_account_id, $cash_accounts)) {
                $_profile['status'] = 'danger';
                $_profile['status_description'] = _wp('Счет, в который ранее производился импорт, более не существует. Перезапустите импорт заново');
                $plugin->saveProfile($_profile_id, $_profile);
            }
            $_profile['update_date'] = (empty($_profile['last_update_time']) ? '' : wa_date('humandatetime', $_profile['last_update_time']));
            /** данные для продолжения прогресса импорта */
            if (!empty($profile_run_data[$_profile_id])) {
                $_profile['run_data'] = [
                    'progress' => (int) ifempty($profile_run_data, $_profile_id, 'progress', 0)
                ];
            }
            if (empty($_profile['import_id'])) {
                $_profile['imports'] = [];
            } else {
                $imports = [cash()->getEntityRepository(cashImport::class)->findById($_profile['import_id'])];
                $_profile['imports'] = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, $imports);
            }
        }

        $this->view->assign([
            'plugin_settings'    => $plugin_settings,
            'profiles'           => $profiles,
            'expense_operations' => $plugin->getConfigParam('expense'),
            'income_operations'  => $plugin->getConfigParam('income'),
            'categories'         => $categories,
            'cash_accounts'      => $cash_accounts,
            'error'              => waRequest::get('error', '', waRequest::TYPE_STRING_TRIM)
        ]);
    }
}
