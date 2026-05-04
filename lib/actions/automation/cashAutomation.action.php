<?php

/**
 * cashAutomationAction
 */
class cashAutomationAction extends cashViewAction
{
    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        wa('shop');
        $this->view->assign([
            'events'           => $this->getEvents(),
            'conditions'       => $this->getConditions(),
            'actions'          => $this->getActions(),
            'automation_rules' => $this->getRules(),
        ]);
    }

    private function getEvents()
    {
        return [
            'transaction_add'    => _w('Add transaction'),
            'transaction_update' => _w('Update transaction'),
            'transaction_delete' => _w('Delete transaction'),
        ];
    }

    private function getConditions()
    {
        $conditions = [
            ''            => ['name' => _w('Select condition...'), 'operators' => []],
            'amount'      => ['name' => _w('Сумма операции'), 'operators' => ['>', '<', '=']],
            'description' => ['name' => _w('Описание операции'), 'operators' => ['=', '!=', '%...%']],
            'account_id'  => ['name' => _w('Счёт'), 'operators' => ['=', '!=']],
            'category_id' => ['name' => _w('Статья'), 'operators' => ['=', '!=']],
            'date'        => ['name' => _w('Дата операции'), 'operators' => ['<', '>']],
        ];
        $plugin_conditions = [];
        $conditions += $plugin_conditions;

        return $conditions;
    }

    private function getActions()
    {
        $actions = [
            'self_update'        => _w('Обновить эту же операцию (с которой произошло действие)'),
            'self_delete'        => _w('Удалить эту операцию'),
            'create_transaction' => _w('Создать новую операцию'),
            'other_update'       => _w('Обновить другую операцию'),
            'send_mail'          => _w('Отправить письмо'),
            'action_ss'          => _w('Сделать действие с заказом ШС'),
        ];
        $plugin_actions = [];
        $actions += $plugin_actions;

        return $actions;
    }

    private function getRules()
    {
        $automation_model = new cashAutomationModel();
        $rules = $automation_model->getRules();

        foreach ($rules as &$_rule) {
            if (isset($_rule['rule_data']['user_id'])) {
                $user = new waContact($_rule['rule_data']['user_id']);
                $_rule['rule_data']['user_name'] = waContactNameField::formatName($user);
            }
        }

        return $rules;
    }
}
