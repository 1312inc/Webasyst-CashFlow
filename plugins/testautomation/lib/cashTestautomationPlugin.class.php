<?php

class cashTestautomationPlugin extends waPlugin
{
    private static function getConditions()
    {
        return [
            'testautomation_by_user' => ['name' => 'Имя пользователя', 'operators' => ['==', '<>', '??']],
            'testautomation_by_inn'  => ['name' => 'ИНН', 'operators' => ['^...$', '==', '!=']],
            'testautomation_by_bank' => ['name' => 'Банк', 'operators' => ['#...#', '==', '!=']],
        ];
    }

    private static function getActions()
    {
        return  [
            'testautomation_repeat_transaction' => 'Создавать рекурентную операцию',
            'testautomation_send_sms'           => 'Отправить СМС',
            'testautomation_create_reminder'    => 'Создать напоминание',
        ];
    }

    public function cashEventViewTestautomationHandler()
    {
        return [
            'conditions' => self::getConditions(),
            'actions' => self::getActions()
        ];
    }

    /**
     * @param $params
     * @return bool
     */
    public function cashEventTestautomationHandler($params = [])
    {
        $conditions = self::getConditions();
        $condition = ifset($params, 'condition', 'condition_id', '');
        if (empty($conditions[$condition])) {
            cash()->getLogger()->log(['В плагине нет такого условия для выполнения', 'PARAMS' => $params], cashHelper::AUTOMATION_LOG);
            return false;
        }
        $actions = self::getActions();
        $action = ifset($params, 'action', '');
        if (empty($actions[$action])) {
            cash()->getLogger()->log(['В плагине нет такого действия для выполнения', 'PARAMS' => $params], cashHelper::AUTOMATION_LOG);
            return false;
        }

        cash()->getLogger()->log(['Плагин выполнил: '.$actions[$action].' C условием: '.var_export($conditions[$condition], true), 'PARAMS' => $params], cashHelper::AUTOMATION_LOG);

        return true;
    }
}
