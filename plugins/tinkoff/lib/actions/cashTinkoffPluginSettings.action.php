<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $profile_id = waRequest::request('profile_id', null, waRequest::TYPE_STRING_TRIM);

        /** https://developer.tinkoff.ru/products/scenarios/account-info#категории-операций */
        $categories_operations = [
            'cardOperation'      => 'Оплата картой',
            'cashOut'            => 'Снятие наличных',
            'fee'                => 'Услуги банка',
            'penalty'            => 'Штрафы',
            'contragentPeople'   => 'Исходящие платежи',
            'selfIncomeOuter'    => 'Перевод себе в другой банк (исходящий платеж)',
            'selfTransferOuter'  => 'Перевод между своими счетами в Тинькофф Бизнес (исходящий платеж)',
            'salary'             => 'Выплаты (исходящий платеж)',
            'contragentOutcome'  => 'Перевод контрагенту (исходящий платеж)',
            'contragentRefund'   => 'Возврат контрагенту (исходящий платеж)',
            'budget'             => 'Платежи в бюджет',
            'tax'                => 'Налоговые платежи',
            'creditPaymentOuter' => 'Погашение кредита',
            'sme-c2c'            => 'С карты на карту',
            'otherOut'           => 'Другое',
            'unspecifiedOut'     => 'Без категории',
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
            'profiles'      => $profiles,
            'operations'    => $categories_operations,
            'categories'    => $categories,
            'cash_accounts' => $cash_accounts
        ]);
    }
}
