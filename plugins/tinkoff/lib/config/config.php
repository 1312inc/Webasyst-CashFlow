<?php

return [
    /** https://developer.tinkoff.ru/products/scenarios/account-info#категории-операций */
    /** Исходящие */
    'expense' => [
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
        'unspecifiedOut'     => _wp('Без категории')
    ],
    /** Входящие (income) */
    'income' => [
        'incomePeople'          => _wp('Входящие платежи'),
        'selfTransferInner'     => _wp('Перевод между своими счетами в Тинькофф Бизнес (входящий платеж)'),
        'selfOutcomeOuter'      => _wp('Перевод себе из другого банка (входящий платеж)'),
        'contragentIncome'      => _wp('Пополнение от контрагента (входящий платеж)'),
        'acquiring'             => _wp('Эквайринг'),
        'incomeLoan'            => _wp('Получение кредита'),
        'refundIn'              => _wp('Возврат средств'),
        'cashIn'                => _wp('Взнос наличных'),
        'cashInRevenue'         => _wp('Взнос выручки из кассы (взнос наличных)'),
        'cashInOwn'             => _wp('Взнос собственных средств (взнос наличных)'),
        'income'                => _wp('Проценты на остаток по счёту'),
        'depositPartWithdrawal' => _wp('Частичное изъятие средств депозита'),
        'depositFullWithdrawal' => _wp('Закрытие депозитного счёта ЮЛ'),
        'creditPaymentInner'    => _wp('Погашение кредита'),
        'otherIn'               => _wp('Другое'),
        'unspecifiedIn'         => _wp('Без категории')
    ],
    /** Automapping */
    'key_words' => [
        /** Исходящие */
        'cardOperation' => [],
        'cashOut' => [],
        'fee' => [],
        'penalty' => ['Штраф', 'Штрафы'],
        'contragentPeople' => [],
        'selfIncomeOuter' => [],
        'selfTransferOuter' => [],
        'salary' => [],
        'contragentOutcome' => [],
        'contragentRefund' => [],
        'budget' => [],
        'tax' => ['Налог', 'Налоги'],
        'creditPaymentOuter' => [],
        'sme-c2c' => ['На карту'],
        'otherOut' => ['Другое'],
        'unspecifiedOut' => ['Без категории'],

        /** Входящие (income) */
        'incomePeople' => [],
        'selfTransferInner' => [],
        'selfOutcomeOuter' => [],
        'contragentIncome' => [],
        'acquiring' => ['Эквайринг', 'Продажи'],
        'incomeLoan' => [],
        'refundIn' => [],
        'cashIn' => [],
        'cashInRevenue' => [],
        'cashInOwn' => [],
        'income' => [],
        'depositPartWithdrawal' => [],
        'depositFullWithdrawal' => [],
        'creditPaymentInner' => [],
        'otherIn' => ['Другое'],
        'unspecifiedIn' => ['Без статьи'],
    ]
];
