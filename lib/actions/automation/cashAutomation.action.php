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
        $this->view->assign([
            'events'           => $this->getEvents(),
            'conditions'       => self::getConditions(),
            'actions'          => self::getActions(),
            'automation_rules' => $this->getRules(),
        ]);
    }

    private function getEvents()
    {
        return [
            'transaction_add'    => _w('New transaction created'),
            'transaction_update' => _w('Exising transaction edited'),
            'transaction_delete' => _w('Transaction deleted'),
        ];
    }

    public static function getConditions()
    {
        return cashAutomation::getConditions() + self::getDataPlugin('conditions');
    }

    public static function getActions()
    {
        return cashAutomation::getActions() + self::getDataPlugin('actions');
    }

    private static function getDataPlugin($name)
    {
        static $plugin_conditions;
        static $plugin_actions;

        if (empty($plugin_conditions) || empty($plugin_actions)) {
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
                        $plugin_conditions["{$plugin_id}_$_condition_id"] = $_condition;
                    }
                }
                if (isset($_data['actions'])) {
                    foreach ($_data['actions'] as $_action_id => $_action) {
                        $plugin_actions["{$plugin_id}_$_action_id"] = [
                            'action' => $_action,
                            'plugin_id' => $plugin_id,
                        ];
                    }
                }
            }
        }

        return ($name === 'conditions' ? $plugin_conditions : $plugin_actions);
    }

    private function getRules()
    {
        $automation_model = new cashAutomationModel();

        return $automation_model->getRules();
    }
}
