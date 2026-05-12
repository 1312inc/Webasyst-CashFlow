<?php

class cashAutomation
{
    const AUTOMATION_LOG = 'automation';

    /**
     * @param $action_object waAPIMethod
     * @param $response array
     * @return array|null
     * @throws waException
     */
    public static function automationEvent($action_object, $response)
    {
        $compare = function ($a, $b, $op) {
            if ($op === '=' || $op === '==' || $op === '===') {
                return $a == $b;
            } elseif ($op === '!=' || $op === '!==' || $op === '<>') {
                return $a != $b;
            } elseif ($op === '>') {
                return $a > $b;
            } elseif ($op === '<') {
                return $a < $b;
            } elseif ($op === '%...%') {
                return stripos($a, $b) !== false;
            }
            return false;
        };
        $map = [
            'cashTransactionCreateMethod' => 'create',
            'cashTransactionUpdateMethod' => 'update',
            'cashTransactionDeleteMethod' => 'delete'
        ];
        $class = get_class($action_object);
        $action = ifset($map, $class, null);
        if (!$action) {
            return null;
        }
        $action_id = 'transaction_'.$action;
        $automation_model = new cashAutomationModel();
        $rules = $automation_model->getByField('action_id', $action_id, true);

        $actions = cashAutomationAction::getActions();
        $conditions = cashAutomationAction::getConditions();

        foreach ($rules as $rule) {
            $condition = ifset($rule, 'conditions', 0, []);
            $condition_id = ifset($condition, 'condition_id', null);
            $rule_data = ifset($rule, 'rule_data', []);
            $rule_action = ifset($rule_data, 'action', null);
            list($app, $plugin_id) = explode('.', $rule['app_id'].'.');
            $found = empty($condition);

            if ($plugin_id) {
                $condition['condition_id'] = str_replace($plugin_id.'_', '', $condition_id);
                $params = [
                    'action_id'   => $action_id,
                    'condition'   => $condition,
                    'action'      => str_replace($plugin_id.'_', '', $rule_action),
                    'transaction' => $response
                ];

                $all_enabled_plugins = wa('cash')->getConfig()->getPlugins();
                if ($method = ifset($all_enabled_plugins, $plugin_id, 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_HANDLE, null)) {
                    try {
                        $found = wa()->getPlugin($plugin_id)->$method($params);
                    } catch (Exception $ex) {
                        cash()->getLogger()->error($ex->getMessage());
                    }
                }
            } elseif (!empty($conditions[$condition_id]) && !empty($actions[$rule_action])) {
                $value = ifset($condition,'value', null);
                $operator = ifset($condition, 'operator', '');
                switch ($condition_id) {
                    case 'amount':
                        if ($compare(ifset($response, 'amount', null), $value, $operator)) {
                            $found = true;
                        }
                        break;
                    case 'description':
                        if ($compare(ifset($response, 'description', null), $value, $operator)) {
                            $found = true;
                        }
                        break;
                    case 'account_id':
                        if ($compare(ifset($response, 'account_id', null), $value, $operator)) {
                            $found = true;
                        }
                        break;
                    case 'category_id':
                        if ($compare(ifset($response, 'category_id', null), $value, $operator)) {
                            $found = true;
                        }
                        break;
                    case 'date':
                        if ($compare(ifset($response, 'date', null), $value, $operator)) {
                            $found = true;
                        }
                        break;
                    default:
                }
                if ($found) {
                    switch ($rule_action) {
                        case 'self_update':
                        case 'self_delete':
                        case 'create_transaction':
                        case 'other_update':
                        case 'send_mail':
                        case 'action_ss':
                        default:
                            cash()->getLogger()->log(['Действие "'.ifset($actions, $rule_action, 'action', 'NULL').'" выполнено', 'RULE' => $rule, 'TRANSACTION' => $response], self::AUTOMATION_LOG);
                    }
                }
            }

            if ($found) {
                break;
            }
        }

        return [];
    }
}