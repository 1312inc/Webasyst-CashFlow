<?php

class cashTestautomationPlugin extends waPlugin
{
    private static function getConditions()
    {
        return [
            'is_sunny' => ['name' => 'Плагин: Если светит солнце', 'operators' => ['==' => '==', '!=' => '!='], 'select' => ['clear' => 'Ясно', 'cloudy' => 'Малооблачно', 'gloomy' => 'Пасмурно']],
        ];
    }

    private static function getActions()
    {
        return [
            'create_ozon' => ['action' => 'Плагин: Создать новую операцию с тестовой комиссией Ozon'] + self::getElements('create_ozon'),
            'create_wb'   => ['action' => 'Плагин: Создать новую операцию с тестовой комиссией Wildberries'] + self::getElements('create_wb'),
        ];
    }

    /**
     * Вызывается для встраивания в меню по событию backend_automation_view
     *
     * @return array
     */
    public function cashEventViewTestautomationHandler()
    {
        return [
            'conditions' => self::getConditions(),
            'actions' => self::getActions()
        ];
    }

    /**
     * Этот метод вызывается для проверки каждого условия, если условие относится к плагину.
     * Выполняется ли, сохраненное в таблице автоматизации, предоставленное плагином условие из getConditions()?
     * $params[
     *      event_id     -> одно из значений transaction_add/transaction_update/transaction_delete
     *      action       -> один из ключей cashAutomation::getActions(), self::getActions() или другого плагина
     *      condition    -> условие для проверки плагином
     *      transaction  -> массив с транзакцией
     *      key1 => val1 -> Сохраненные значения дополнительных полей из self::getElements()
     *      ...
     *      keyN => valN
     * ]
     * В ответе, метод возвращает true, если условие по мнению плагина истинное
     *
     * @return boolean
     */
    public function cashIsConditionTrueTestautomationHandler($params = [])
    {
        $result = false;
        $conditions = self::getConditions();
        $condition_id = ifset($params, 'condition', 'condition_id', null);
        $operator = ifset($params, 'condition', 'operator', null);
        $value = ifset($params, 'condition', 'value', null);
        $transaction = ifset($params, 'transaction', []);
        if (empty($conditions[$condition_id])) {
            return false;
        }

        switch ($condition_id) {
            case 'by_user':
            case 'by_inn':
            case 'by_bank':
                // проверяем выполнение условия для этих ключей
                $result = true;
                break;
            case 'is_sunny':
                $result = $this->isSunny($operator, $value, $transaction);
        }

        return $result;
    }

    /**
     * Вызывается для выполнения действия после выполнения всех условий
     * по событию backend_automation_handle для конкретного плагина
     *
     *  $params[
     *       event_id     -> одно из значений transaction_add/transaction_update/transaction_delete
     *       action       -> один из ключей cashAutomation::getActions(), self::getActions() или другого плагина
     *       conditions   -> массив со всеми условиями правила
     *       transaction  -> массив с транзакцией
     *       key1 => val1 -> Сохраненные значения дополнительных полей из self::getElements()
     *       ...
     *       keyN => valN
     *  ]
     *  В ответе, метод возвращает true, если действие плагином выполнено успешно
     *
     * @param $params
     * @return bool
     * @throws waException
     */
    public function cashEventTestautomationHandler($params = [])
    {
        $actions = self::getActions();
        $action = ifset($params, 'action', '');
        $transaction = ifset($params, 'transaction', []);
        if (empty($actions[$action])) {
            cash()->getLogger()->log(['В плагине нет такого действия для выполнения', 'PARAMS' => $params], cashAutomation::AUTOMATION_LOG);
            return false;
        }
        if ($action === 'create_wb' || $action === 'create_ozon') {
            $c = ($action === 'create_wb' ? $this->getWildberriesCommission() : $this->getOzonCommission());
            $commission = $c[array_rand($c)];

            $desc = sprintf(
                'Комиссия %s %s%% в размере %s от суммы %s. По состоянию на дату: %s. ',
                $action === 'create_wb' ? 'Wildberries' : 'Ozon',
                $commission,
                ($transaction['amount']/100)*$commission,
                $transaction['amount'],
                date(ifset($params, $action.'_date_format', 'Y=m=D'))
            ).ifset($params, $action.'_description', '');

            $new_transaction = (cash()->getEntityFactory(cashTransaction::class))->createNew();
            $new_transaction->setAmount(($transaction['amount']/100)*$commission);
            $new_transaction->setDescription($desc);
            $new_transaction->setDate(date('Y-m-d'));
            $new_transaction->setAccountId(ifset($transaction, 'account_id', null));
            $new_transaction->setCategoryId(ifset($params, $action.'_expense_category', null));

            $saver = new cashTransactionSaver();
            $saver->addToPersist($new_transaction);
            $saver->persistTransactions();

            cash()->getLogger()->log(['Плагин выполнил: '.$actions[$action]['action'], 'PARAMS' => $params], cashAutomation::AUTOMATION_LOG);
            return true;
        }

        return false;
    }

    /**
     * Считаем прибыль как "Ясно", а расход как "Пасмурно"
     *
     * @param $operator
     * @param $value
     * @param $transaction
     * @return boolean
     */
    private function isSunny($operator, $value, $transaction)
    {
        $amount = $transaction['amount'];
        if ($amount == 0) {
            $_value = 'cloudy';
        } else {
            $_value = ($amount > 0 ? 'clear' : 'gloomy');
        }

        if ($operator === '==' && $value == $_value) {
            return true;
        } elseif ($operator === '!=' &&  $value != $_value) {
            return true;
        }

        return false;
    }

    /**
     * Получение комиссий от Ozon
     *
     * @return array
     */
    private function getOzonCommission()
    {
        return ['20', '14', '4.49', '3.49', '2.39', '1.19'];
    }

    /**
     * Получение комиссий от Wildberries
     *
     * @return array
     */
    private function getWildberriesCommission()
    {
        return ['25.5', '27.5', '30.0', '33.4'];
    }

    /**
     * Добавление расширенных полей в "Действия"
     *
     * @param $type
     * @return array[]
     * @throws waException
     */
    private static function getElements($type)
    {
        $elements = [];
        switch ($type) {
            case 'create_ozon':
            case 'create_wb':
                $categories = cash()->getModel(cashCategory::class)->getByTypeForContact(cashCategory::TYPE_EXPENSE);
                $elements = [
                    $type.'_expense_category' => [
                        'type' => 'select',
                        'label' => 'Категория для операции',
                        'options' => ['' => 'Выбрать категорию'] + array_combine(array_column($categories, 'id'), array_column($categories, 'name'))
                    ],
                    $type.'_personal' => [
                        'type' => 'checkbox',
                        'label' => 'Согласие',
                        'value' => 'check_personal',
                        'text' => 'Согласен на обработку персональных данных'
                    ],
                    $type.'_date_format' => [
                        'type' => 'radio',
                        'text' => 'Формат даты',
                        'options' => [
                            'Y-m-d' => 'YYYY-MM-DD',
                            'd/m/Y' => 'DD/MM/YYYY',
                            'm/d Y' => 'MM/DD YYYY',
                        ]
                    ],
                    $type.'_description' => [
                        'type' => 'textarea',
                        'label' => 'Дополнение к описанию',
                        'hint' => 'Пример дополнительного описания hint'
                    ]
                ];
                break;
        }

        return ['elements' => $elements];
    }
}
