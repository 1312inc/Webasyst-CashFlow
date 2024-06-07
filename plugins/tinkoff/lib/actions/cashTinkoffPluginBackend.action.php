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
        $review_widget = false;

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa()->getPlugin('tinkoff');
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContactWithCounter(wa()->getUser());
        $root_path = $this->getConfig()->getRootPath();
        $plugin_settings = $plugin->getSettings();
        $profiles = ifset($plugin_settings, 'profiles', []);
        $profile_run_data = $this->getStorage()->read('profile_run_data');
        foreach ($profiles as $_profile_id => &$_profile) {
            /** проверяем аккаунты */
            $cash_account_id = (int) ifset($_profile, 'cash_account', 0);
            if ($cash_account_id && !array_key_exists($cash_account_id, $cash_accounts)) {
                $_profile['status'] = 'danger';
                $_profile['status_description'] = _wp('Счет, в который ранее производился импорт, более не существует. Перезапустите импорт заново.');
                $plugin->saveProfile($_profile_id, $_profile);
            }
            $_profile['update_date'] = (empty($_profile['last_update_time']) ? '' : wa_date('humandatetime', $_profile['last_update_time']));
            $_profile['cron_command'] = "php $root_path/cli.php cash tinkoffTransaction $_profile_id";

            /** данные для продолжения прогресса импорта */
            if (!empty($profile_run_data[$_profile_id])) {
                $_profile['run_data'] = [
                    'progress' => (int) ifempty($profile_run_data, $_profile_id, 'progress', 0)
                ];
            }
            $_profile['imports'] = [];
            if (!empty($_profile['import_id'])) {
                $import = cash()->getEntityRepository(cashImport::class)->findById($_profile['import_id']);
                if ($import instanceof cashAbstractEntity) {
                    $_profile['imports'] = cashDtoFromEntityFactory::fromEntities(cashImportDto::class, [$import]);
                }
            }
            if (!$review_widget && ifset($_profile, 'first_update', true) === false) {
                $review_widget = true;
            }
        }

        $this->view->assign([
            'review_widget'      => $review_widget,
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
