<?php

class cashAutomation
{
    const AUTOMATION_LOG = 'automation';

    public static function getConditions()
    {
        return [
            ''            => ['name' => _w('Any transaction'), 'operators' => []],
            'amount'      => ['name' => _w('Amount'), 'operators' => ['>=', '<=', '==']],
            'description' => ['name' => _w('Description'), 'operators' => ['==', '!=', '%...%']],
            'account_id'  => ['name' => _w('Account'), 'operators' => ['==', '!=']],
            'category_id' => ['name' => _w('Category'), 'operators' => ['==', '!=']],
            'date'        => ['name' => _w('Date'), 'operators' => ['<=', '>=']],
        ];
    }

    public static function getActions()
    {
        return [
            ''                   => ['action' => _w('Change ...')],
            'self_update'        => ['action' => _w('Update self...')] + self::getElements('self_update'),
//            'other_update'       => ['action' => _w('Update another...')],
//            'self_delete'        => ['action' => _w('Delete self')],
//            'create_transaction' => ['action' => _w('Create new...')],
            'send_mail'          => ['action' => _w('Send email...')] + self::getElements('send_mail'),
        ] + (wa()->appExists('shop') ? ['action_ss' => ['action' => _w('Shop-Script...')] + self::getElements('action_ss')] : []);
    }

    /**
     * @param $action_object waAPIMethod
     * @param $transaction array
     * @return array|null
     * @throws waException
     */
    public static function automationEvent($action_object, $transaction)
    {
        $compare = function ($a, $b, $op) {
            if ($op === '=' || $op === '==' || $op === '===') {
                return $a == $b;
            } elseif ($op === '!=' || $op === '!==' || $op === '<>') {
                return $a != $b;
            } elseif ($op === '>=') {
                return $a >= $b;
            } elseif ($op === '<=') {
                return $a <= $b;
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
        $transaction = (array) (empty($transaction[0]) ? $transaction : reset($transaction));
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
                            'event_id'    => $action_id,
                            'condition'   => $_condition,
                            'action'      => str_replace($plugin_id.'_', '', $rule_action),
                            'transaction' => $transaction
                        ] + $rule_data;
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
                            $amount = ifset($transaction, 'amount', null);
                            if (isset($amount, $value) && $compare(abs($amount), abs($value), $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'description':
                            if ($compare(ifset($transaction, 'description', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'account_id':
                            if ($compare(ifset($transaction, 'account_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'category_id':
                            if ($compare(ifset($transaction, 'category_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'date':
                            if ($compare(ifset($transaction, 'date', null), $value, $operator)) {
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
                                'event_id'    => $action_id,
                                'action'      => str_replace($plugin_id.'_', '', $rule_action),
                                'transaction' => $transaction,
                                'conditions'  => array_map(function ($_condition) {
                                    if (!empty($_condition['plugin_id'])) {
                                        $_condition['condition_id'] = str_replace($_condition['plugin_id'].'_', '', $_condition['condition_id']);
                                    }
                                    return $_condition;
                                }, $conditions)
                            ] + $rule_data;
                            if (wa()->getPlugin($rule_data['plugin_id'])->$method($params)) {
                                cash()->getLogger()->log(['Действие плагином выполнено', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                            }
                        } catch (Exception $ex) {
                            cash()->getLogger()->error($ex->getMessage());
                        }
                    } else {
                        cash()->getLogger()->log(['Плагин и/или его метод не определены', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                    }
                } else {
                    $done = false;
                    switch ($rule_action) {
                        case 'self_update':
                            $transaction_property = ifset($rule_data, 'transaction_property', null);
                            $property_value = ifset($rule_data, 'property_value', null);
                            /** @var cashTransaction $transaction_obj */
                            $transaction_obj = cash()->getEntityRepository(cashTransaction::class)->findById($transaction['id']);
                            if ($transaction_obj && $transaction_property && $property_value) {
                                switch ($transaction_property) {
                                    case 'amount':
                                        $transaction_obj->setAmount($property_value);
                                        break;
                                    case 'description':
                                        $transaction_obj->setDescription($property_value);
                                        break;
                                    case 'account_id':
                                        if ((cash()->getModel(cashAccount::class))->getById($property_value)) {
                                            $transaction_obj->setAccountId($property_value);
                                        } else {
                                            cash()->getLogger()->log(['Счет для редактируемой операции не найден', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                                        }
                                        break;
                                    case 'category_id':
                                        if ((cash()->getModel(cashCategory::class))->getById($property_value)) {
                                            $transaction_obj->setCategoryId($property_value);
                                        } else {
                                            cash()->getLogger()->log(['Статья для редактируемой операции не найдена', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                                        }
                                        break;
                                }

                                try {
                                    $saver = new cashTransactionSaver();
                                    $saver->addToPersist($transaction_obj);
                                    if ($saver->persistTransactions()) {
                                        $done = true;
                                    }
                                } catch (Exception $ex) {
                                    cash()->getLogger()->log(['Ошибка во время обновления операции', 'RULE' => $rule, 'TRANSACTION' => $transaction, 'ERROR' => $ex->getMessage()], self::AUTOMATION_LOG);
                                }
                            } else {
                                cash()->getLogger()->log(['Редактируемая операция не найдена и/или не задано обновляемое свойство и/или его значение', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                            }
                            break;
                        case 'self_delete':
                        case 'create_transaction':
                        case 'other_update':
                        case 'send_mail':
                        case 'action_ss':
//                            cash()->getLogger()->log(['Действие "'.ifset($known_actions, $rule_action, 'action', 'NULL').'" выполнено', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                            break;
                        default:
                            cash()->getLogger()->log(['Неизвестное действие', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                    }
                    if ($done) {
                        cash()->getLogger()->log(['Действие "'.ifset($known_actions, $rule_action, 'action', 'NULL').'" выполнено', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                    }
                }
            }
        }

        return [];
    }

    /**
     * @param $type
     * @return array
     */
    private static function getElements($type)
    {
        $elements = [];
        switch ($type) {
            case 'send_mail':
                $elements = [
                    'from' => [
                        'type' => 'email',
                        'label' => _w('Кому')
                    ],
                    'text' => [
                        'type' => 'textarea',
                        'label' => _w('Текст сообщения')
                    ]
                ];
                break;
            case 'self_update':
                $elements = [
                    'transaction_property' => [
                        'type' => 'select',
                        'label' => _w('Свойство операции'),
                        'options' => [
                            'amount' => _w('Сумма'),
                            'category_id' => _w('Статья'),
                            'account_id' => _w('Счёт'),
                            'description' => _w('Описание'),
                        ]
                    ],
                    'property_value' => [
                        'type' => 'text',
                    ]
                ];
                break;
            case 'action_ss':
                wa('shop');
                $workflow = new shopWorkflow();
                $actions = $workflow->getAvailableActions();
                $elements = [
                    'ss_action' => [
                        'type' => 'select',
                        'label' => _w('Действие с заказом'),
                        'options' => array_combine(array_keys($actions), array_column($actions, 'name'))
                    ],
                    'hint_ss' => [
                        'type' => 'hint',
                        'text' => _w('действиен с заказом выполнится, только если операция с каким-то заказом связана + для этого заказа действие применимо')
                    ]
                ];
                break;
        }


        return ['elements' => $elements];
    }
}
