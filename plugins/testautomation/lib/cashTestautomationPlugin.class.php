<?php

class cashTestautomationPlugin extends waPlugin
{
    private static function getConditions()
    {
        return [
            'by_user' => ['name' => 'Имя пользователя', 'operators' => ['==', '<>', '??']],
            'by_inn'  => ['name' => 'ИНН', 'operators' => ['^...$', '==', '!=']],
            'by_bank' => ['name' => 'Банк', 'operators' => ['#...#', '==', '!=']],
        ];
    }

    private static function getActions()
    {
        return  [
            'repeat_transaction' => 'Создавать рекурентную операцию',
            'send_sms'           => 'Отправить СМС',
            'create_reminder'    => 'Создать напоминание',
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
     * Вызывается для проверки каждого условия, если условие относится к плагину.
     * Выполняется ли, сохраненное в таблице автоматизации, предоставленное плагином условие из getConditions()?
     *
     * @return false
     */
    public function cashIsConditionTrueTestautomationHandler()
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
