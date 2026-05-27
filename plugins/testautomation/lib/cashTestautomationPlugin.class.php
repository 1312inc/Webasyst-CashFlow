<?php

class cashTestautomationPlugin extends waPlugin
{
    private static function getConditions()
    {
        return [
            'by_user'  => ['name' => 'Плагин: Имя пользователя', 'operators' => ['==', '<>', '??']],
            'by_inn'   => ['name' => 'Плагин: ИНН', 'operators' => ['^...$', '==', '!=']],
            'by_bank'  => ['name' => 'Плагин: Банк', 'operators' => ['#...#', '==', '!=']],
            'is_sunny' => ['name' => 'Плагин: Если светит солнце', 'operators' => ['==', '!='], 'select' => ['clear' => 'Ясно', 'cloudy' => 'Малооблачно', 'gloomy' => 'Пасмурно']],
        ];
    }

    private static function getActions()
    {
        return [
            'repeat_transaction' => 'Плагин: Создавать рекурентную операцию',
            'send_sms'           => 'Плагин: Отправить СМС',
            'create_reminder'    => 'Плагин: Создать напоминание',
            'create_ozon'        => 'Плагин: Создать новую операцию с комиссией Ozon',
            'create_wb'          => 'Плагин: Создать новую операцию с комиссией Wildberries',
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
     *      'action_id'   -> одно из значений transaction_add/transaction_update/transaction_delete
     *      'action'      -> один из ключей cashAutomation::getActions(), self::getActions() или другого плагина
     *      'condition'   -> условие для проверки плагином
     *      'transaction' -> массив с транзакцией
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
     * @param $params
     * @return bool
     */
    public function cashEventTestautomationHandler($params = [])
    {
        $actions = self::getActions();
        $action = ifset($params, 'action', '');
        if (empty($actions[$action])) {
            cash()->getLogger()->log(['В плагине нет такого действия для выполнения', 'PARAMS' => $params], cashAutomation::AUTOMATION_LOG);
            return false;
        }

        cash()->getLogger()->log(['Плагин выполнил: '.$actions[$action], 'PARAMS' => $params], cashAutomation::AUTOMATION_LOG);

        return true;
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
}
