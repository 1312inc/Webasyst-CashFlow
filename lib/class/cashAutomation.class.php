<?php

class cashAutomation
{
    const AUTOMATION_LOG = 'automation';

    public static function getConditions()
    {
        return [
            ''                => ['name' => _w('Any transaction'), 'operators' => []],
            'amount'          => ['name' => _w('Amount'), 'operators' => ['>=', '<=', '==']],
            'description'     => ['name' => _w('Description'), 'operators' => ['==', '!=', '%...%']],
            'account_id'      => ['name' => _w('Account'), 'operators' => ['==', '!='], 'select' => self::getAccounts()],
            'category_id'     => ['name' => _w('Category'), 'operators' => ['==', '!='], 'select' => self::getCategories()],
            'date'            => ['name' => _w('Date'), 'operators' => ['<=', '>='], 'type' => 'date'],
            'external_id'     => ['name' => _w('External ID'), 'operators' => ['==', '!='], 'type' => 'number'],
            'external_source' => ['name' => _w('External source'), 'operators' => ['==', '!='], 'select' => self::externalSource()],
        ];
    }

    public static function getActions()
    {
        return [
            ''                   => ['action' => _w('Выбрать действие')],
            'self_update'        => ['action' => _w('Update self...')] + self::getElements('self_update'),
//            'other_update'       => ['action' => _w('Update another...')],
//            'self_delete'        => ['action' => _w('Delete self')],
            'create_transaction' => ['action' => _w('Create new...')] + self::getElements('create_transaction'),
            'send_mail'          => ['action' => _w('Send email...')] + self::getElements('send_mail'),
        ] + (wa()->appExists('shop') ? ['action_ss' => ['action' => _w('Shop-Script...')] + self::getElements('action_ss')] : []);
    }

    /**
     * @return array
     * @throws waException
     */
    private static function getCategories(): array
    {
        $categories = cash()->getModel(cashCategory::class)->getAllActiveForContact();

        return array_combine(array_column($categories, 'id'), array_column($categories, 'name'));
    }

    /**
     * @return array
     * @throws waException
     */
    private static function getAccounts(): array
    {
        $accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());

        return array_combine(array_column($accounts, 'id'), array_column($accounts, 'name'));
    }

    private static function externalSource(): array
    {
        $sources = cash()->getModel(cashTransaction::class)
            ->select('DISTINCT external_source')
            ->where('external_source IS NOT NULL')->fetchAll();

        return array_combine(array_column($sources, 'external_source'), array_column($sources, 'external_source'));
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
                        case 'external_id':
                            if ($compare(ifset($transaction, 'external_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'external_source':
                            if ($compare(ifset($transaction, 'external_source', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
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
                        case 'self_delete':
                        case 'other_update':
                            break;
                        case 'create_transaction':
                            $done = self::selfUpdate($rule, $transaction, true);
                            break;
                        case 'self_update':
                            $done = self::selfUpdate($rule, $transaction);
                            break;
                        case 'send_mail':
                            $done = self::sendMail($rule, $transaction);
                            break;
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
     * @return array[]
     * @throws waException
     */
    private static function getElements($type)
    {
        $elements = [];
        switch ($type) {
            case 'send_mail':
                $elements = [
                    'email_to' => [
                        'type' => 'email',
                        'label' => _w('Кому')
                    ],
                    'text' => [
                        'type' => 'textarea',
                        'label' => _w('Текст сообщения')
                    ]
                ];
                break;
            case 'create_transaction':
            case 'self_update':
                $elements = [
                    $type.'_header' => [
                        'type' => 'header',
                        'text' => _w('Свойства операции')
                    ],
                    $type.'_property_date' => [
                        'type' => 'date',
                        'label' => _w('Дата операции')
                    ],
                    $type.'_property_account_id' => [
                        'type' => 'select',
                        'label' => _w('Счёт'),
                        'options' => ['' => 'Выбрать счет'] + self::getAccounts()
                    ],
                    $type.'_property_category_id' => [
                        'type' => 'select',
                        'label' => _w('Статья'),
                        'options' => ['' => 'Выбрать статью'] + self::getCategories()
                    ],
                    $type.'_property_amount' => [
                        'type' => 'text',
                        'label' => _w('Сумма')
                    ],
                    $type.'_property_description' => [
                        'type' => 'textarea',
                        'label' => _w('Комментарий'),
                        'hint' => _w('Переменные').'<br>
                            <b>{ID} - </b>'._w('ИД операции').'<br>
                            <b>{DATE} - </b>'._w('Дата операции').'<br>
                            <b>{ACCOUNT_ID} - </b>'._w('Счёт операции').'<br>
                            <b>{CATEGORY_ID} - </b>'._w('Статья операции').'<br>
                            <b>{AMOUNT} - </b>'._w('Сумма операции').'<br>
                            <b>{DESCRIPTION} - </b>'._w('Комментарий к операции').'<br>
                            <b>{CREATE_CONTACT_ID} - </b>'._w('операции').'<br>
                            <b>{CREATE_DATETIME} - </b>'._w('Дата создания операции').'<br>
                            <b>{UPDATE_DATETIME} - </b>'._w('Дата обновления операции').'<br>
                            <b>{IS_ARCHIVED} - </b>'._w('В архиве ли операции').'<br>
                            <b>{EXTERNAL_SOURCE} - </b>'._w('Источник операции').'<br>
                            <b>{EXTERNAL_ID} - </b>'._w('ИД источника операции').'<br>
                            <b>{CONTRACTOR_CONTACT_ID} - </b>'._w('Плательщик операции').'<br>
                        '
                    ],
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
                        'hint' => _w('действиен с заказом выполнится, только если операция с каким-то заказом связана + для этого заказа действие применимо'),
                        'options' => array_combine(array_keys($actions), array_column($actions, 'name'))
                    ]
                ];
                break;
        }

        return ['elements' => $elements];
    }

    /**
     * @param $rule
     * @param $transaction
     * @param $is_new
     * @return bool
     * @throws waException
     */
    private static function selfUpdate($rule, $transaction, $is_new = false)
    {
        $result = false;

        /** @var cashTransaction $transaction_obj */
        $transaction_obj = ($is_new ? (cash()->getEntityFactory(cashTransaction::class))->createNew() : cash()->getEntityRepository(cashTransaction::class)->findById($transaction['id']));
        if ($transaction_obj) {
            $type = ifset($rule, 'rule_data', 'action', '');
            $properties = ifset($rule, 'rule_data', []);
            $transaction_obj->setUpdateDatetime(date('Y-m-d H:i:s'));
            foreach ($properties as $_property_name => $property_value) {
                $property = str_replace($type.'_property_', '', $_property_name);
                switch ($property) {
                    case 'date':
                        $transaction_obj->setDate($property_value);
                        $transaction_obj->setDatetime($property_value.' 00:00:00');
                        break;
                    case 'amount':
                        $transaction_obj->setAmount($property_value);
                        break;
                    case 'account_id':
                        if ((cash()->getModel(cashAccount::class))->getById($property_value)) {
                            $transaction_obj->setAccountId($property_value);
                        } else {
                            cash()->getLogger()->log(['Счет для операции не найден', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                        }
                        break;
                    case 'category_id':
                        if ((cash()->getModel(cashCategory::class))->getById($property_value)) {
                            $transaction_obj->setCategoryId($property_value);
                        } else {
                            cash()->getLogger()->log(['Статья для операции не найдена', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                        }
                        break;
                    case 'description':
                        $patterns = [
                            '#\{ID}#',
                            '#\{DATE}#',
                            '#\{ACCOUNT_ID}#',
                            '#\{CATEGORY_ID}#',
                            '#\{AMOUNT}#',
                            '#\{DESCRIPTION}#',
                            '#\{CREATE_CONTACT_ID}#',
                            '#\{CREATE_DATETIME}#',
                            '#\{UPDATE_DATETIME}#',
                            '#\{IS_ARCHIVED}#',
                            '#\{EXTERNAL_SOURCE}#',
                            '#\{EXTERNAL_ID}#',
                            '#\{CONTRACTOR_CONTACT_ID}#',
                        ];
                        $replacements = [
                            ifset($transaction, 'id', ''),                     //{ID}
                            ifset($transaction, 'date', ''),                   //{DATE}
                            ifset($transaction, 'account_id', ''),             //{ACCOUNT_ID}
                            ifset($transaction, 'category_id', ''),            //{CATEGORY_ID}
                            ifset($transaction, 'amount', ''),                 //{AMOUNT}
                            ifset($transaction, 'description', ''),            //{DESCRIPTION}
                            ifset($transaction, 'create_contact_id', ''),      //{CREATE_CONTACT_ID}
                            ifset($transaction, 'create_datetime', ''),        //{CREATE_DATETIME}
                            ifset($transaction, 'update_datetime', ''),        //{UPDATE_DATETIME}
                            ifset($transaction, 'is_archived', ''),            //{IS_ARCHIVED}
                            ifset($transaction, 'external_source', ''),        //{EXTERNAL_SOURCE}
                            ifset($transaction, 'external_id', ''),            //{EXTERNAL_ID}
                            ifset($transaction, 'contractor_contact_id', ''),  //{CONTRACTOR_CONTACT_ID}
                        ];
                        $property_value = preg_replace($patterns, $replacements, $property_value);
                        $transaction_obj->setDescription($property_value);
                        break;
                }
            }

            try {
                $saver = new cashTransactionSaver();
                $saver->addToPersist($transaction_obj);
                if ($saver->persistTransactions()) {
                    $result = true;
                }
            } catch (Exception $ex) {
                cash()->getLogger()->log(['Ошибка во время обновления операции', 'RULE' => $rule, 'TRANSACTION' => $transaction, 'ERROR' => $ex->getMessage()], self::AUTOMATION_LOG);
            }


        } else {
            cash()->getLogger()->log(['Редактируемая операция не найдена и/или не задано обновляемое свойство и/или его значение', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
        }

        return $result;
    }

    private static function sendMail($rule, $transaction)
    {
        $result = false;
        $email_to = ifset($rule, 'rule_data', 'email_to', null);
        $text = ifset($rule, 'rule_data', 'text', null);
        if ($email_to && $text) {
            try {
                $subject = _w('Оповещение о срабатывании');
                $message = new waMailMessage($subject, $text);
                $message->setFrom(wa()->getSetting('email', '', 'webasyst'));
                $message->setTo($email_to);
                $result = $message->send();
                if (!$result) {
                    cash()->getLogger()->log(['Письмо не отправлено', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
                }
            } catch (Exception $ex) {
                cash()->getLogger()->log(['Ошибка во время отправки письма', 'RULE' => $rule, 'TRANSACTION' => $transaction, 'ERROR' => $ex->getMessage()], self::AUTOMATION_LOG);
            }
        } else {
            cash()->getLogger()->log(['Письмо не отправлено, так как не задан адрес и/или текст', 'RULE' => $rule, 'TRANSACTION' => $transaction], self::AUTOMATION_LOG);
        }

        return $result;
    }
}
