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
                    $_condition['plugin_id'] = $plugin_id;
                    $this->plugin_conditions["{$plugin_id}_$_condition_id"] = $_condition;
                }
            }
            if (isset($_data['actions'])) {
                foreach ($_data['actions'] as $_action_id => $_action) {
                    $this->plugin_actions["{$plugin_id}_$_action_id"] = [
                        'action' => $_action,
                        'plugin_id' => $plugin_id,
                    ];
                }
            }
        }

        $this->view->assign([
            'events'           => $this->getEvents(),
            'conditions'       => self::getConditions() + ($this->plugin_conditions ?: []),
            'actions'          => self::getActions() + ($this->plugin_actions ?: []),
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

    public static function getConditions()
    {
        return [
            ''            => ['name' => _w('Select condition...'), 'operators' => []],
            'amount'      => ['name' => _w('Сумма операции'), 'operators' => ['>', '<', '=']],
            'description' => ['name' => _w('Описание операции'), 'operators' => ['=', '!=', '%...%']],
            'account_id'  => ['name' => _w('Счёт'), 'operators' => ['=', '!=']],
            'category_id' => ['name' => _w('Статья'), 'operators' => ['=', '!=']],
            'date'        => ['name' => _w('Дата операции'), 'operators' => ['<', '>']],
        ];
    }

    public static function getActions()
    {
        return [
            'self_update'        => ['action' => _w('Обновить эту же операцию (с которой произошло действие)')],
            'self_delete'        => ['action' => _w('Удалить эту операцию')],
            'create_transaction' => ['action' => _w('Создать новую операцию')],
            'other_update'       => ['action' => _w('Обновить другую операцию')],
            'send_mail'          => ['action' => _w('Отправить письмо')],
            'action_ss'          => ['action' => _w('Сделать действие с заказом ШС')],
        ];
    }

    private function getRules()
    {
        $automation_model = new cashAutomationModel();

        return $automation_model->getRules();
    }
}
