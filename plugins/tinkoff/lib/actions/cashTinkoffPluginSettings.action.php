<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $profile_id = waRequest::request('profile_id', null, waRequest::TYPE_STRING_TRIM);

        /** https://developer.tinkoff.ru/products/scenarios/account-info#категории-операций */
        $categories_operations = [
            'cardOperation'      => _wp('Оплата картой'),
            'cashOut'            => _wp('Снятие наличных'),
            'fee'                => _wp('Услуги банка'),
            'penalty'            => _wp('Штрафы'),
            'contragentPeople'   => _wp('Исходящие платежи'),
            'selfIncomeOuter'    => _wp('Перевод себе в другой банк (исходящий платеж)'),
            'selfTransferOuter'  => _wp('Перевод между своими счетами в Тинькофф Бизнес (исходящий платеж)'),
            'salary'             => _wp('Выплаты (исходящий платеж)'),
            'contragentOutcome'  => _wp('Перевод контрагенту (исходящий платеж)'),
            'contragentRefund'   => _wp('Возврат контрагенту (исходящий платеж)'),
            'budget'             => _wp('Платежи в бюджет'),
            'tax'                => _wp('Налоговые платежи'),
            'creditPaymentOuter' => _wp('Погашение кредита'),
            'sme-c2c'            => _wp('С карты на карту'),
            'otherOut'           => _wp('Другое'),
            'unspecifiedOut'     => _wp('Без категории'),
        ];

        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa('cash')->getPlugin('tinkoff');
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

        $this->view->assign([
            'profile_id'    => $profile_id,
            'profiles'      => $this->pasteCronCommand($profiles),
            'operations'    => $categories_operations,
            'categories'    => $categories,
            'cash_accounts' => $cash_accounts
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
