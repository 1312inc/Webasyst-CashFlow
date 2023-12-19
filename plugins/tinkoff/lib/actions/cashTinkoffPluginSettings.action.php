<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
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
        $profile = waRequest::get('profile', 1, waRequest::TYPE_INT);
        $plugin = wa('cash')->getPlugin('tinkoff');
        $settings = $plugin->getSettings($profile);
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();
        $cash_accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());

        $this->view->assign([
            'settings'      => $settings,
            'operations'    => $categories_operations,
            'categories'    => $categories,
            'cash_accounts' => $cash_accounts
        ]);
    }
}
