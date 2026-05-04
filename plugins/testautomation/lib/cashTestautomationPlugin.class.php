<?php

class cashTestautomationPlugin extends waPlugin
{
    public function cashEventViewTestautomationHandler()
    {
        return [
            'conditions' => [
                'by_user' => ['name' => 'Имя пользователя', 'operators' => ['==', '<>', '??']],
                'by_inn'  => ['name' => 'ИНН', 'operators' => ['^...$', '==', '!=']],
                'by_bank' => ['name' => 'Банк', 'operators' => ['#...#', '==', '!=']],
            ],
            'actions' => [
                'repeat_transaction' => 'Создавать рекурентную операцию',
                'send_sms'           => 'Отправить СМС',
                'create_reminder'    => 'Создать напоминание',
            ]
        ];
    }

    public function cashEventTestautomationHandler()
    {
        return [];
    }
}
