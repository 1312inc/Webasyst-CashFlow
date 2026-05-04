<?php

/**
 * cashAutomationAction
 */
class cashAutomationAction extends cashViewAction
{
    private $plugin_conditions = [];
    private $plugin_actions = [];

    public function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        parent::preExecute();
    }

    public function runAction($params = null)
    {
        /**
         * @event backend_automation_view
         * @since 4.0.0
         *
         * @return cashEvent
         */
        $event = new cashEvent(cashEventStorage::WA_BACKEND_AUTOMATION_VIEW);
        $event_result = cash()->waDispatchEvent($event);

        foreach ($event_result as $plugin_id => $_data) {
            $plugin_id = preg_replace('#-plugin$#', '', $plugin_id);
            if (isset($_data['conditions'])) {
                foreach ($_data['conditions'] as $_condition_id => $_condition) {
                    $this->plugin_conditions["{$plugin_id}_$_condition_id"] = $_condition;
                }
            }
            if (isset($_data['actions'])) {
                foreach ($_data['actions'] as $_action_id => $_action) {
                    $this->plugin_actions["{$plugin_id}_$_action_id"] = $_action;
                }
            }
        }

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
        if ($this->plugin_conditions) {
            $conditions += $this->plugin_conditions;
        }

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
        if ($this->plugin_actions) {
            $actions += $this->plugin_actions;
        }

        return $actions;
    }

    private function getRules()
    {
        $automation_model = new cashAutomationModel();

        return $automation_model->getRules();
    }
}
