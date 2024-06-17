<?php

return [
    /** https://developer.tbank.ru/products/scenarios/account-info#категории-операций */
    /** Исходящие (expense) */
    'expense' => [
        'cardOperation'      => _wp('Оплата картой'),
        'cashOut'            => _wp('Снятие наличных'),
        'fee'                => _wp('Услуги банка'),
        'penalty'            => _wp('Штрафы'),
        'contragentPeople'   => _wp('Исходящие платежи'),
        'selfIncomeOuter'    => _wp('Перевод себе в другой банк'),
        'selfTransferOuter'  => _wp('Перевод между своими счетами в Т-Бизнесе'),
        'salary'             => _wp('Выплаты'),
        'contragentOutcome'  => _wp('Перевод контрагенту'),
        'contragentRefund'   => _wp('Возврат контрагенту'),
        'budget'             => _wp('Платежи в бюджет'),
        'tax'                => _wp('Налоговые платежи'),
        'creditPaymentOuter' => _wp('Погашение кредита'),
        'sme-c2c'            => _wp('С карты на карту'),
        'otherOut'           => _wp('Другое'),
        'unspecifiedOut'     => _wp('Без категории')
    ],
    /** Входящие (income) */
    'income' => [
        'incomePeople'          => _wp('Входящие платежи'),
        'selfTransferInner'     => _wp('Перевод между своими счетами в Т-Бизнесе'),
        'selfOutcomeOuter'      => _wp('Перевод себе из другого банка'),
        'contragentIncome'      => _wp('Пополнение от контрагента'),
        'acquiring'             => _wp('Эквайринг'),
        'incomeLoan'            => _wp('Получение кредита'),
        'refundIn'              => _wp('Возврат средств'),
        'cashIn'                => _wp('Взнос наличных'),
        'cashInRevenue'         => _wp('Взнос выручки из кассы'),
        'cashInOwn'             => _wp('Взнос собственных средств'),
        'income'                => _wp('Проценты на остаток по счёту'),
        'depositPartWithdrawal' => _wp('Частичное изъятие средств депозита'),
        'depositFullWithdrawal' => _wp('Закрытие депозитного счёта ЮЛ'),
        'creditPaymentInner'    => _wp('Погашение кредита'),
        'otherIn'               => _wp('Другое'),
        'unspecifiedIn'         => _wp('Без категории')
    ]
];
