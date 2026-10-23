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
        $automation_rules = $this->getRules();
        $conditions = self::getConditions();

        foreach ($automation_rules as &$rule) {
            if (!empty($rule['conditions'])) {
                foreach ($rule['conditions'] as &$rule_condition) {
                    $rule_condition['badge'] = '';
                    if ($condition_data = ifset($conditions[$rule_condition['condition_id']])) {
                        $operator = ifset($condition_data, 'elements', 'operator', 'options', $rule_condition['operator'], '');
                        $value = ifset($rule_condition, 'value', 'k');
                        $sub_value = ifset($rule_condition, $value, '');
                        if (empty($sub_value)) {
                            $sub_value = ifset($condition_data, 'elements', 'value', 'options', $value, '');
                        }
                        $rule_condition['badge'] = ifset($condition_data, 'name', '').' '.$operator.' '.($sub_value ?: $value);
                    }
                }
            }
        }
        unset($rule, $rule_condition);

        $this->view->assign([
            'events'           => $this->getEvents(),
            'conditions'       => $conditions,
            'actions'          => self::getActions(),
            'automation_rules' => $automation_rules,
        ]);
    }

    private function getEvents()
    {
        return [
            'transaction_add'    => _w('Added'),
            'transaction_update' => _w('Updated'),
            'transaction_delete' => _w('Deleted'),
        ];
    }

    /**
     * @return array
     * @throws waException
     */
    public static function getConditions(): array
    {
        return cashAutomation::getConditions() + self::getDataPlugin('conditions');
    }

    /**
     * @return array
     * @throws waException
     */
    public static function getActions(): array
    {
        return cashAutomation::getActions() + self::getDataPlugin('actions');
    }

    /**
     * @param $name
     * @return array
     * @throws waException
     */
    private static function getDataPlugin($name): array
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
                        $plugin_actions["{$plugin_id}_$_action_id"] = ['plugin_id' => $plugin_id] + $_action;
                    }
                }
            }
        }

        return (array) ($name === 'conditions' ? $plugin_conditions : $plugin_actions);
    }

    private function getRules()
    {
        $automation_rules = (new cashAutomationModel())->getRules();
        if ($automation_rules) {
            $logs_info = (new cashAutomationLogModel())->getInfoLogs(array_keys($automation_rules));
            foreach ($automation_rules as $_automation_id => $_automation_rule) {
                $automation_rules[$_automation_id] += [
                    'count_log' => (int) ifempty($logs_info, $_automation_id, 'count_log', 0),
                    'last_date_log' => cashHelper::convertDateToISO8601(ifempty($logs_info, $_automation_id, 'last_date_log', null)),
                ];
            }
        }

        return $automation_rules;
    }
}
