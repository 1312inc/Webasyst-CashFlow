<?php

class cashTestautomationPlugin extends waPlugin
{
    private static function getConditions()
    {
        return [
            'by_user'  => ['name' => 'Плагин: Имя пользователя', 'operators' => ['==', '<>', '??']],
            'by_inn'   => ['name' => 'Плагин: ИНН', 'operators' => ['^...$', '==', '!=']],
            'by_bank'  => ['name' => 'Плагин: Банк', 'operators' => ['#...#', '==', '!=']],
            'is_sunny' => ['name' => 'Плагин: Если светит солнце', 'operators' => ['==', '!='], 'select' => ['Ясно', 'Малооблачно', 'Пасмурно']],
        ];
    }

    private static function getActions()
    {
        return  [
            'fetch_commission'   => 'Плагин: Получить и сохранить комиссию маркетплейса',
            'repeat_transaction' => 'Плагин: Создавать рекурентную операцию',
            'send_sms'           => 'Плагин: Отправить СМС',
            'create_reminder'    => 'Плагин: Создать напоминание',
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
        return true;
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
}
