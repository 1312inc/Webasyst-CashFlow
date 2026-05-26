<?php

class cashAutomation
{
    const AUTOMATION_LOG = 'automation';

    public static function getConditions()
    {
        return [
            ''            => ['name' => _w('Configure...'), 'operators' => []],
            'amount'      => ['name' => _w('Amount'), 'operators' => ['>', '<', '=']],
            'description' => ['name' => _w('Description'), 'operators' => ['=', '!=', '%...%']],
            'account_id'  => ['name' => _w('Account'), 'operators' => ['=', '!=']],
            'category_id' => ['name' => _w('Category'), 'operators' => ['=', '!=']],
            'date'        => ['name' => _w('Date'), 'operators' => ['<', '>']],
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
            'action_ss'          => ['action' => _w('Выполнить действие с заказом ШС')],
        ];
    }

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
            'cashTransactionCreateMethod' => 'add',
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
        $response = (array) (empty($response[0]) ? $response : reset($response));
        $known_actions = cashAutomationAction::getActions();
        $known_conditions = cashAutomationAction::getConditions();
        $all_enabled_plugins = wa('cash')->getConfig()->getPlugins();

        foreach ($rules as $rule) {
            $condition_done = 0;
            $conditions = ifset($rule, 'conditions', []);
            $rule_data = ifset($rule, 'rule_data', []);
            $rule_action = ifset($rule_data, 'action', null);

            foreach ($conditions as $_condition) {
                $condition_id = ifset($_condition, 'condition_id', null);

                if ($plugin_id = ifset($_condition, 'plugin_id', null)) {
                    if ($method = ifset($all_enabled_plugins, $plugin_id, 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_IS_TRUE, null)) {
                        $_condition['condition_id'] = str_replace($plugin_id.'_', '', $condition_id);
                        $params = [
                            'action_id'   => $action_id,
                            'condition'   => $_condition,
                            'action'      => str_replace($plugin_id.'_', '', $rule_action),
                            'transaction' => $response
                        ];
                        try {
                            if (wa()->getPlugin($plugin_id)->$method($params)) {
                                $condition_done++;
                            }
                        } catch (Exception $ex) {
                            cash()->getLogger()->error($ex->getMessage());
                        }
                    }
                } elseif (!empty($known_conditions[$condition_id]) && !empty($known_actions[$rule_action])) {
                    $value = ifset($_condition,'value', null);
                    $operator = ifset($_condition, 'operator', '');
                    switch ($condition_id) {
                        case 'amount':
                            $amount = ifset($response, 'amount', null);
                            if (isset($amount, $value) && $compare(abs($amount), abs($value), $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'description':
                            if ($compare(ifset($response, 'description', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'account_id':
                            if ($compare(ifset($response, 'account_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'category_id':
                            if ($compare(ifset($response, 'category_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'date':
                            if ($compare(ifset($response, 'date', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        default:
                    }
                }
            }
            if (count($conditions) === $condition_done) {
                if (!empty($rule_data['plugin_id'])) {
                    if ($method = ifset($all_enabled_plugins, $rule_data['plugin_id'], 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_HANDLE, null)) {
                        try {
                            $params = [
                                'action_id'   => $action_id,
                                'action'      => str_replace($rule_data['plugin_id'].'_', '', $rule_action),
                                'transaction' => $response
                            ];
                            if (wa()->getPlugin($rule_data['plugin_id'])->$method($params)) {
                                cash()->getLogger()->log(['Действие плагином выполнено', 'RULE' => $rule, 'TRANSACTION' => $response], self::AUTOMATION_LOG);
                            }
                        } catch (Exception $ex) {
                            cash()->getLogger()->error($ex->getMessage());
                        }
                    } else {
                        cash()->getLogger()->log(['Плагин и/или его метод не определены', 'RULE' => $rule, 'TRANSACTION' => $response], self::AUTOMATION_LOG);
                    }
                } else {
                    switch ($rule_action) {
                        case 'self_update':
                        case 'self_delete':
                        case 'create_transaction':
                        case 'other_update':
                        case 'send_mail':
                        case 'action_ss':
                            cash()->getLogger()->log(['Действие "'.ifset($known_actions, $rule_action, 'action', 'NULL').'" выполнено', 'RULE' => $rule, 'TRANSACTION' => $response], self::AUTOMATION_LOG);
                            break;
                        default:
                            cash()->getLogger()->log(['Неизвестное действие', 'RULE' => $rule, 'TRANSACTION' => $response], self::AUTOMATION_LOG);
                    }
                }
            }
        }

        return [];
    }
}