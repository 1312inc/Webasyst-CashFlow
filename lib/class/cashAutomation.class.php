<?php

class cashAutomation
{
    const AUTOMATION_LOG = 'automation';

    public static function getConditions()
    {
        return [
            ''                  => ['name' => _w('Add condition...')] + self::getElements(),
            'amount'            => ['name' => _w('Amount')] + self::getElements('amount'),
            'description'       => ['name' => _w('Description')] + self::getElements('description'),
            'account_id'        => ['name' => _w('Account')] + self::getElements('account_id'),
            'category_id'       => ['name' => _w('Category')] + self::getElements('category_id'),
            'date_transaction'  => ['name' => _w('Date')] + self::getElements('date_transaction'),
            'create_contact_id' => ['name' => _w('User')] + self::getElements('create_contact_id'),
            'external_id'       => ['name' => _w('External ID')] + self::getElements('external_id'),
            'external_source'   => ['name' => _w('External source')] + self::getElements('external_source'),
        ];
    }

    public static function getActions()
    {
        return [
            ''                   => ['action' => _w('')],
            'create_transaction' => ['action' => _w('Create new transaction')] + self::getElements('create_transaction'),
            'self_update'        => ['action' => _w('Update self')] + self::getElements('self_update'),
            //            'self_delete'        => ['action' => _w('Delete self')],
            //            'other_update'       => ['action' => _w('Update another transaction...')], // which one?
            'send_mail'          => ['action' => _w('Send email')] + self::getElements('send_mail'),
        ] + (wa()->appExists('shop') ? ['action_ss' => ['action' => _w('Shop-Script...')] + self::getElements('action_ss')] : []);
    }

    /**
     * @param int|null $id
     * @return array|string
     * @throws waException
     */
    private static function getCategories($id = null)
    {
        static $categories = [];
        if (empty($categories)) {
            $categories = [
                cashCategory::TYPE_INCOME => [],
                cashCategory::TYPE_EXPENSE => [],
            ];
            foreach (cash()->getModel(cashCategory::class)->getAllActiveForContact() as $_category) {
                if ($_category['type'] === cashCategory::TYPE_INCOME) {
                    $categories[cashCategory::TYPE_INCOME][$_category['id']] = ifempty($_category, 'name', '');
                } elseif ($_category['type'] === cashCategory::TYPE_EXPENSE) {
                    $categories[cashCategory::TYPE_EXPENSE][$_category['id']] = ifempty($_category, 'name', '');
                } else {
                    $categories[cashCategory::TYPE_TRANSFER][$_category['id']] = ifempty($_category, 'name', '');
                }
            }
        }
        if ($id) {
            $c = $categories[cashCategory::TYPE_INCOME] + $categories[cashCategory::TYPE_EXPENSE] + $categories[cashCategory::TYPE_TRANSFER];
            return ifset($c, $id, []);
        }

        return [
            _w('Income') => $categories[cashCategory::TYPE_INCOME],
            _w('Expense') => $categories[cashCategory::TYPE_EXPENSE],
            _w('Transfer') => $categories[cashCategory::TYPE_TRANSFER]
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws waException
     */
    private static function getAccounts($id = null): array
    {
        static $accounts = [];
        if (empty($accounts)) {
            $accounts = cash()->getModel(cashAccount::class)->getAllActiveForContact(wa()->getUser());
            $accounts = array_combine(array_column($accounts, 'id'), $accounts);
        }
        if ($id) {
            return ifset($accounts, $id, []);
        }

        return array_combine(array_keys($accounts), array_column($accounts, 'name'));
    }

    private static function externalSource(): array
    {
        $sources = cash()->getModel(cashTransaction::class)
            ->select('DISTINCT external_source')
            ->where('external_source IS NOT NULL')->fetchAll();

        return array_combine(array_column($sources, 'external_source'), array_column($sources, 'external_source'));
    }

    /**
     * @return cashAutomationLogModel
     */
    private static function getLog(): cashAutomationLogModel
    {
        static $log_model;
        if (empty($log_model)) {
            $log_model = new cashAutomationLogModel();
        }

        return $log_model;
    }

    /**
     * @param $a
     * @param $b
     * @param $op
     * @return bool
     */
    private static function compare($a, $b, $op): bool
    {
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
    }

    /**
     * @param $action_object waAPIMethod
     * @param $transaction array
     * @return array|null
     * @throws waException
     */
    public static function automationEvent($action_object, $transaction)
    {
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

        $account = self::getAccounts($transaction['account_id']);
        $transaction['account_name'] = ifset($account, 'name', null);
        $transaction['currency'] = ifset($account, 'currency', null);
        $transaction['category_name'] = self::getCategories($transaction['category_id']);

        if (!empty($transaction['contractor_contact_id'])) {
            $contractor_contact = new waContact($transaction['contractor_contact_id']);
            $transaction['contractor_name'] = $contractor_contact->getName();
        }

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
                            self::getLog()->add($rule, $transaction, $ex->getMessage(), 'error');
                        }
                    }
                } elseif (!empty($known_conditions[$condition_id]) && !empty($known_actions[$rule_action])) {
                    $value = ifset($_condition,'value', null);
                    $operator = ifset($_condition, 'operator', '');
                    switch ($condition_id) {
                        case 'amount':
                            $amount = ifset($transaction, 'amount', null);
                            if (isset($amount, $value) && $value !== '' && self::compare(abs($amount), abs($value), $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'description':
                            $is_contains = $operator === '%%';
                            $description = (string) ifset($transaction, 'description', '');

                            if ($value === 'ss_order') {
                                $transaction['ss_order_id'] = 0;
                                if (wa()->appExists('shop')) {
                                    $order_format = mb_strtolower(str_replace('{$order.id}', '', wa('shop')->getConfig()->getOrderFormat()));
                                    preg_match('#.*?[№\#\s]*'.preg_quote($order_format).'(?<ss_order>\d+).*#u', mb_strtolower($description), $matches);
                                    $ss_order_id = (int) ifset($matches, 'ss_order', 0);
                                    if ($is_contains && $ss_order_id) {
                                        $transaction['ss_order_id'] = $ss_order_id;
                                        $condition_done++;
                                    } elseif (!$is_contains && !$ss_order_id) {
                                        $condition_done++;
                                    }
                                }
                            } elseif ($value === 'custom_text') {
                                if (preg_match('#\b'.preg_quote(ifset($_condition,'custom_text', '')).'\b#u', $description)) {
                                    $condition_done++;
                                }
                            } elseif ($value === 'compare_date') {
                                $transaction['date_from_description'] = '';
                                $months = [
                                    'января' => 1,
                                    'февраля' => 2,
                                    'марта' => 3,
                                    'апреля' => 4,
                                    'мая' => 5,
                                    'июня' => 6,
                                    'июля' => 7,
                                    'августа' => 8,
                                    'сентября' => 9,
                                    'октября' => 10,
                                    'ноября' => 11,
                                    'декабря' => 12,
                                ];

                                preg_match('#.*?(?<date>(?<date_b>\d+)[-./\s]+(?<month>[[:alnum:]]+)[-./\s]+(?<date_e>\d+)).*#u', mb_strtolower($description), $matches);
                                $date = ifset($matches, 'date', null);
                                if ($is_contains && $date) {
                                    $month = ifset($matches, 'month', null);
                                    if (!empty($months[$month])) {
                                        $date = $matches['date_b'].'-'.$months[$month].'-'.$matches['date_e'];
                                    }
                                    try {
                                        $timestamp = strtotime($date);
                                        $transaction['date_from_description'] = date('Y-m-d', $timestamp);
                                        $condition_done++;
                                    } catch (Exception $ex) {
                                        self::getLog()->add($rule, $transaction, _w('Could not fetch date from the transaction description.').$ex->getMessage(), 'error');
                                    }
                                } elseif (!$is_contains && !$date) {
                                    $condition_done++;
                                }
                            }
                            break;
                        case 'account_id':
                            if (self::compare(ifset($transaction, 'account_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'category_id':
                            if (self::compare(ifset($transaction, 'category_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'date_transaction':
                            $today = date('Y-m-d');
                            $date = ifset($transaction, 'date', null);
                            if (
                                ($operator === 'is_today' && self::compare($date, $today, '=='))
                                || ($operator === 'is_future' && self::compare($date, $today, '>'))
                                || ($operator === 'is_past' && self::compare($date, $today, '<'))
                                || self::compare($date, $value, $operator)
                            ) {
                                $condition_done++;
                            }
                            break;
                        case 'create_contact_id':
                            if (self::compare(ifset($transaction, 'create_contact_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'external_id':
                            if (self::compare(ifset($transaction, 'external_id', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                        case 'external_source':
                            if (self::compare(ifset($transaction, 'external_source', null), $value, $operator)) {
                                $condition_done++;
                            }
                            break;
                    }
                }
            }
            if (count($conditions) === $condition_done) {
                if (cashHelper::isPremium()) {
                    if (!empty($rule_data['plugin_id'])) {
                        if ($method = ifset($all_enabled_plugins, $rule_data['plugin_id'], 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_HANDLE, null)) {
                            try {
                                $params = [
                                    'event_id'    => $action_id,
                                    'action'      => str_replace($rule_data['plugin_id'].'_', '', $rule_action),
                                    'transaction' => $transaction,
                                    'conditions'  => array_map(function ($_condition) {
                                        if (!empty($_condition['plugin_id'])) {
                                            $_condition['condition_id'] = str_replace($_condition['plugin_id'].'_', '', $_condition['condition_id']);
                                        }
                                        return $_condition;
                                    }, $conditions)
                                ] + $rule_data;
                                if (wa()->getPlugin($rule_data['plugin_id'])->$method($params)) {
                                    if ($method = ifset($all_enabled_plugins, $rule_data['plugin_id'], 'handlers', cashEventStorage::WA_BACKEND_AUTOMATION_VIEW, null)) {
                                        $plugin_view = wa()->getPlugin($rule_data['plugin_id'])->$method();
                                        $plugin_view = ifset($plugin_view, 'actions', $params['action'], 'action', null);
                                    }
                                    self::getLog()->add($rule, $transaction, sprintf_wp('Plugin action completed: %s', (empty($plugin_view) ? '' : $plugin_view)));
                                }
                            } catch (Exception $ex) {
                                self::getLog()->add($rule, $transaction, $ex->getMessage(), 'error');
                            }
                        } else {
                            self::getLog()->add($rule, $transaction, _w('Unknown plugin action'), 'warning');
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
                                $done = self::actionSS($rule, $transaction);
                                break;
                            default:
                                self::getLog()->add($rule, $transaction, _w('Unknown bot action'));
                        }
                        if ($done) {
                            self::getLog()->add($rule, $transaction, sprintf_wp('Bot action executed: %s', ifset($known_actions, $rule_action, 'action', _w('Unknown bot action'))));
                        }
                    }
                } else {
                    self::getLog()->add($rule, $transaction, _w('Bot action skipped: premium required'));
                }
            }
        }

        return [];
    }

    private static function getTextVariables()
    {
        return _w('Origin transaction values:').'<br>
            <b>{ID}</b> — '._w('Transaction ID').'<br>
            <b>{DATE}</b> — '._w('Transaction date').'<br>
            <b>{AMOUNT}</b> — '._w('Transaction amount').'<br>
            <b>{CURRENCY}</b> — '._w('Currency').'<br>
            <b>{DESCRIPTION}</b> — '._w('Comment').'<br>
            <b>{ACCOUNT_ID}</b> — '._w('Account ID').'<br>
            <b>{ACCOUNT_NAME}</b> — '._w('Account name').'<br>
            <b>{CATEGORY_ID}</b> — '._w('Category ID').'<br>
            <b>{CATEGORY_NAME}</b> — '._w('Category name').'<br>
            <b>{CONTRACTOR_CONTACT_ID}</b> — '._w('Contractor contact ID').'<br>
            <b>{CONTRACTOR_NAME}</b> — '._w('Contractor name').'<br>
            <b>{EXTERNAL_SOURCE}</b> — '._w('External origin app name (for transactions imported from an external source or app)').'<br>
            <b>{EXTERNAL_ID}</b> — '._w('External entity ID (for transactions imported from an external source or app)').'<br>
        ';
    }

    private static function replaceVariables($text = '', $transaction = [])
    {
        static $patterns = [
            '#\{ID}#',
            '#\{DATE}#',
            '#\{ACCOUNT_ID}#',
            '#\{ACCOUNT_NAME}#',
            '#\{CATEGORY_ID}#',
            '#\{CATEGORY_NAME}#',
            '#\{AMOUNT}#',
            '#\{CURRENCY}#',
            '#\{DESCRIPTION}#',
            '#\{CREATE_CONTACT_ID}#',
            '#\{CREATE_DATETIME}#',
            '#\{UPDATE_DATETIME}#',
            '#\{CONTRACTOR_CONTACT_ID}#',
            '#\{CONTRACTOR_NAME}#',
            '#\{IS_ARCHIVED}#',
            '#\{EXTERNAL_SOURCE}#',
            '#\{EXTERNAL_ID}#',
        ];
        $replacements = [
            ifset($transaction, 'id', ''),                     //{ID}
            ifset($transaction, 'date', ''),                   //{DATE}
            ifset($transaction, 'account_id', ''),             //{ACCOUNT_ID}
            ifset($transaction, 'account_name', ''),           //{ACCOUNT_NAME}
            ifset($transaction, 'category_id', ''),            //{CATEGORY_ID}
            ifset($transaction, 'category_name', ''),          //{CATEGORY_NAME}
            ifset($transaction, 'amount', ''),                 //{AMOUNT}
            ifset($transaction, 'currency', ''),               //{CURRENCY}
            ifset($transaction, 'description', ''),            //{DESCRIPTION}
            ifset($transaction, 'create_contact_id', ''),      //{CREATE_CONTACT_ID}
            ifset($transaction, 'create_datetime', ''),        //{CREATE_DATETIME}
            ifset($transaction, 'update_datetime', ''),        //{UPDATE_DATETIME}
            ifset($transaction, 'contractor_contact_id', ''),  //{CONTRACTOR_CONTACT_ID}
            ifset($transaction, 'contractor_name', ''),        //{CONTRACTOR_NAME}
            ifset($transaction, 'is_archived', ''),            //{IS_ARCHIVED}
            ifset($transaction, 'external_source', ''),        //{EXTERNAL_SOURCE}
            ifset($transaction, 'external_id', ''),            //{EXTERNAL_ID}
        ];

        return preg_replace($patterns, $replacements, $text);
    }

    /**
     * @param string $type
     * @return array[]
     * @throws waException
     */
    private static function getElements($type = '')
    {
        static $contacts = [];
        $elements = [];
        switch ($type) {
            case 'send_mail':
                $elements = [
                    $type.'_email_to' => [
                        'type' => 'email',
                        'label' => _w('To'),
                        'class' => 'bold long',
                        'hint' => _w('Recipient email address')
                    ],
                    $type.'_email_subject' => [
                        'type' => 'text',
                        'label' => _w('Subject'),
                        'class' => 'long',
                        'hint' => _w('Variables from the body field are supported')
                    ],
                    $type.'_text' => [
                        'type' => 'textarea',
                        'class' => 'width-100',
                        'label' => _w('Body'),
                        'hint' => self::getTextVariables()
                    ]
                ];
                break;
            case 'create_transaction':
            case 'self_update':
                if (empty($contacts)) {
                    $request = new cashApiContactGetListRequest(0,cashApiContactGetListRequest::MAX_LIMIT);
                    list($total, $data) = (new cashApiContactGetListHandler())->handle($request);

                    /** $_contact cashApiContactGetListDto */
                    foreach ((array) $data as $_contact) {
                        $contacts[$_contact->getId()] = ($_contact->getName() ?: _w('Contact ID: ').$_contact->getId());
                    }
                }
                $elements = [
                    $type.'_property_empty_hint' => [
                        'type' => 'header',
                        'text' => _w('To keep the original value, simply leave the action setting empty.')
                    ],
                    $type.'_property_account_id' => [
                        'type' => 'select',
                        'label' => _w('Account'),
                        'options' => ['' => ''] + self::getAccounts()
                    ],
                    $type.'_property_category_id' => [
                        'type' => 'select',
                        'label' => _w('Category'),
                        'options' => ['' => ''] + self::getCategories()
                    ],
                    $type.'_property_contractor' => [
                        'type' => 'select',
                        'label' => _w('Contractor'),
                        'class' => 'contractor_type',
                        'options' => ['' => '', 'contractor_id' => _w('Custom contact ID...')] + $contacts,
                        'child' => 1
                    ],
                    $type.'_property_contractor_id' => [
                        'type' => 'text',
                        'class' => 'property_contractor_id number shorter',
                    ],
                    $type.'_property_amount_type' => [
                        'type' => 'select',
                        'label' => _w('Amount'),
                        'class' => 'amount_type',
                        'options' => ['amount_not_touch' => _w(''), 'amount_fix' => _w('='), 'amount_percent' => '%'],
                        'child' => 1,
                    ],
                    $type.'_property_amount' => [
                        'type' => 'text',
                        'class' => 'property_amount number shorter',
                        'hint' => ''
                    ],
                    $type.'_property_description' => [
                        'type' => 'textarea',
                        'label' => _w('Comment'),
                        'hint' => self::getTextVariables()
                    ],
                    $type.'_script' => [
                        'type' => 'script',
                        'script' => self::getScript('self_update')
                    ]
                ];
                break;
            case 'action_ss':
                wa('shop');
                $actions = [];
                $workflow = new shopWorkflow();
                foreach ($workflow->getAvailableActions() as $id => $_action) {
                    $actions[$id] = _wd('shop', $_action['name']);
                }
                $elements = [
                    'ss_order_id_hint' => [
                        'type' => 'header',
                        'text' => _w('Order ID is always fetched from the original transaction description. Make sure to have IF condition set for the transaction description field to contain Order ID.')
                    ],
                    'ss_action' => [
                        'type' => 'select',
                        'label' => _w('Perform action'),
                        'hint' => _w('Order action will apply only if Order ID is known, and in the selected action is allowed for the order in Shop-Script workflow settings. Otherwise, an email alert will be sent to the failover email specified below.'),
                        'options' => $actions
                    ],
                    'ss_amount_compare' => [
                        'type' => 'checkbox',
                        'label' => _w('Amount'),
                        'value' => '>=',
                        'text' => _w('Require transaction amount to be not less than the actual Shop-Script order amount')
                    ],
                    'ss_customer_compare' => [
                        'type' => 'checkbox',
                        'label' => _w('Customer'),
                        'value' => '==',
                        'text' => _w('Require transaction contractor to match Shop-Script order customer')
                    ],
                    'ss_email_to' => [
                        'type' => 'email',
                        'label' => _w('Failover email'),
                        'class' => 'long',
                        'hint' => _w('If bot conditions were met, but something did not match for the order action to run, an alert with details will be sent to the specified email.')
                    ],
                ];
                break;
            case 'description':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['%%' => _w('contains'), '!%%' => _w('does not contain')],
                    ],
                    'value' => [
                        'type' => 'select',
                        'options' => [
                            'compare_date' => _w('Date'),
                            'custom_text' => _w('Text...')
                        ] + (wa()->appExists('shop') ? ['ss_order' => _w('Order ID')] : [])
                    ],
                    'custom_text' => [
                        'type' => 'text',
                    ],
                    $type.'_script' => [
                        'type' => 'script',
                        'script' => self::getScript('description')
                    ]
                ];
                break;
            case 'amount':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['>=' => '≥', '<=' => '≤', '==' => '=']
                    ],
                    'value' => [
                        'type' => 'text',
                    ]
                ];
                break;
            case 'account_id':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['==' => '=', '!=' => '≠']
                    ],
                    'value' => [
                        'type' => 'select',
                        'options' => self::getAccounts()
                    ]
                ];
                break;
            case 'category_id':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['==' => '=', '!=' => '≠']
                    ],
                    'value' => [
                        'type' => 'select',
                        'options' => self::getCategories()
                    ]
                ];
                break;
            case 'date_transaction':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => [
                            'is_today' => _w('is today'),
                            'is_future' => _w('is in the future'),
                            'is_past' => _w('is in the past'),
                            '<=' => '≤',
                            '>=' => '≥'
                        ]
                    ],
                    'value' => [
                        'type' => 'date',
                    ],
                    $type.'_script' => [
                        'type' => 'script',
                        'script' => self::getScript('date_transaction')
                    ]
                ];
                break;
            case 'create_contact_id':
                $users = waUser::getUsers('cash');
                unset($users[wa()->getUser()->getId()]);
                $users[''] = '';
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['=' => '=', '!=' => '!=']
                    ],
                    'value' => [
                        'type' => 'select',
                        'options' => $users
                    ]
                ];
                break;
            case 'external_id':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['==' => '=', '!=' => '≠']
                    ],
                    'value' => [
                        'type' => 'number',
                    ]
                ];
                break;
            case 'external_source':
                $elements = [
                    'operator' => [
                        'type' => 'select',
                        'options' => ['==' => '=', '!=' => '≠']
                    ],
                    'value' => [
                        'type' => 'select',
                        'options' => self::externalSource()
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
    private static function selfUpdate($rule, $transaction, $is_new = false): bool
    {
        $result = false;

        /** @var cashTransaction $transaction_obj */
        $transaction_obj = ($is_new ? (cash()->getEntityFactory(cashTransaction::class))->createNew() : cash()->getEntityRepository(cashTransaction::class)->findById($transaction['id']));
        if ($transaction_obj) {
            $properties = [];
            $type = ifset($rule, 'rule_data', 'action', '');
            if (!$is_new) {
                $transaction_obj->setUpdateDatetime(date('Y-m-d H:i:s'));
            }

            $date = ifempty($transaction, 'date', date('Y-m-d'));
            $transaction_obj->setDate($date);
            $transaction_obj->setDatetime($date.' 00:00:00');

            foreach (ifset($rule, 'rule_data', []) as $_name => $property_value) {
                $property = str_replace($type.'_property_', '', $_name);
                $properties[$property] = $property_value;
            }

            foreach ($properties as $_property_name => $property_value) {
                switch ($_property_name) {
                    case 'amount':
                        $amount_type = ifset($properties, 'amount_type', 'amount_not_touch');
                        if ($amount_type === 'amount_percent') {
                            $property_value = (float) $property_value / 100;
                            $property_value = $transaction['amount'] * $property_value;
                        } elseif ($amount_type === 'amount_not_touch') {
                            $property_value = $transaction['amount'];
                        }
                        $category_id = ifset($properties, 'category_id', $transaction['category_id']);
                        $category = (cash()->getModel(cashCategory::class))->getById($category_id);
                        if (!empty($category['type'])) {
                            $property_value = abs($property_value) * ($category['type'] === cashCategory::TYPE_INCOME ? 1 : -1);
                        }
                        $transaction_obj->setAmount($property_value);
                        break;
                    case 'account_id':
                        if ((cash()->getModel(cashAccount::class))->getById($property_value)) {
                            $transaction_obj->setAccountId($property_value);
                        } else {
                            self::getLog()->add($rule, $transaction, _w('Account not found'), 'notice');
                        }
                        break;
                    case 'category_id':
                        if ((cash()->getModel(cashCategory::class))->getById($property_value)) {
                            $transaction_obj->setCategoryId($property_value);
                        } else {
                            self::getLog()->add($rule, $transaction, _w('Category not found'), 'notice');
                        }
                        break;
                    case 'description':
                        $property_value = self::replaceVariables($property_value, $transaction);
                        $transaction_obj->setDescription($property_value);
                        break;
                    case 'contractor':
                        if ($property_value === 'contractor_id') {
                            $property_value = ifset($properties, 'contractor_id', 0);
                        }
                        $contractor = new waContact($property_value);
                        if ($contractor->exists()) {
                            $transaction_obj->setContractorContactId((int) $property_value);
                        }
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
                self::getLog()->add($rule, $transaction, _w('Could not update transaction:').' '.$ex->getMessage(), 'error');
            }
        } else {
            self::getLog()->add($rule, $transaction, _w('Could not update transaction: either transaction or one of its fields not found'), 'notice');
        }

        return $result;
    }

    /**
     * @param $rule
     * @param $transaction
     * @return bool
     */
    private static function sendMail($rule, $transaction): bool
    {
        $result = false;
        $type = ifset($rule, 'rule_data', 'action', '');
        $email_to = ifset($rule, 'rule_data', $type.'_email_to', null);
        $text = ifset($rule, 'rule_data', $type.'_text', null);
        $subject = ifset($rule, 'rule_data', $type.'_email_subject', _w('Cash Flow alert'));
        if ($email_to && $text) {
            $subject = self::replaceVariables($subject, $transaction);
            $text = self::replaceVariables($text, $transaction);
            try {
                $message = new waMailMessage($subject, $text);
                $message->setFrom(wa()->getSetting('email', '', 'webasyst'));
                $message->setTo($email_to);
                $result = $message->send();
                if (!$result) {
                    self::getLog()->add($rule, $transaction, _w('Email not sent'), 'notice');
                }
            } catch (Exception $ex) {
                self::getLog()->add($rule, $transaction, _w('Email not sent:').' '.$ex->getMessage(), 'error');
            }
        } else {
            self::getLog()->add($rule, $transaction, _w('Email not sent: either recipient or email content are empty'), 'notice');
        }

        return $result;
    }

    /**
     * @param $rule
     * @param $transaction
     * @return bool
     */
    private static function actionSS($rule, $transaction): bool
    {
        try {
            if (!wa()->appExists('shop')) {
                self::getLog()->add($rule, $transaction, _w('Shop-Script app is not enabled'), 'notice');
                return false;
            }
        } catch (Exception $ex) {
            self::getLog()->add($rule, $transaction, $ex->getMessage(), 'error');
            return false;
        }

        $result = false;
        if ($order_id = ifset($transaction, 'ss_order_id', null)) {
            try {
                $order = new shopOrder($order_id);
                if (!$order->getId()) {
                    self::getLog()->add($rule, $transaction, _w('No Shop-Script order found with the specified ID'), 'notice');
                }
            } catch (Exception $ex) {
                self::getLog()->add($rule, $transaction, $ex->getMessage(), 'error');
                return false;
            }

            $date_compare = true;
            $customer_compare = true;
            $ss_action = ifset($rule, 'rule_data', 'ss_action', null);
            $amount = ifset($transaction, 'amount', null);
            $operator = ifset($rule, 'rule_data', 'ss_amount_compare', '==');
            $amount_compare = self::compare($amount, $order->total, $operator);

            if (ifset($rule, 'rule_data', 'ss_customer_compare', null)) {
                $customer_compare = ifset($transaction, 'contractor_contact_id', '') === $order->contact_id;
            }
            if (isset($transaction['date_from_description'])) {
                $date = substr($order->create_datetime, 0, 10);
                $date_compare = ifset($transaction, 'date_from_description', '') == $date;
            }
            if ($ss_action && $amount_compare && $date_compare && $customer_compare) {
                try {
                    wa('shop', 1);
                    /** @var shopWorkflowAction $action */
                    $workflow = new shopWorkflow();
                    $actions = $workflow->getStateById($order->state_id)->getActions($order);

                    if (ifset($actions, $ss_action, null)) {
                        $action = $workflow->getActionById($ss_action);
                        $result = $action->run($order_id);
                    } else {
                        self::getLog()->add($rule, $transaction, _w('Action is forbidden by Shop-Script workflow for the order').' '.$order_id, 'notice');
                    }
                    wa('cash', 1);
                } catch (Exception $ex) {
                    self::getLog()->add($rule, $transaction, _w('Shop-Script order action failed:').' '.$ex->getMessage(), 'error');
                }
            } elseif ($email_to = ifset($rule, 'rule_data', 'ss_email_to', null)) {
                $notice = [];
                if (!$amount_compare) {
                    $notice[] = _w('Shop-Script order amount and Cash Flow transaction amount won’t match');
                }
                if (!$date_compare) {
                    $notice[] = _w('Shop-Script order date and Cash Flow transaction date won’t match');
                }
                if (!$customer_compare) {
                    $notice[] = _w('Shop-Script order customer and Cash Flow transaction contractor won’t match');
                }
                try {
                    $subject = _w('Cash Flow -> Shop-Script alert');
                    $text = sprintf_wp(
                        'Cash Flow bot is here! Shop-Script order action failed: <b>%s</b>.<br><br> Origin transaction:<br> %s',
                        implode(', ', $notice),
                        var_export($transaction, true)
                    );
                    $message = new waMailMessage($subject, $text);
                    $message->setFrom(wa()->getSetting('email', '', 'webasyst'));
                    $message->setTo($email_to);
                    $result = $message->send();
                    if (!$result) {
                        self::getLog()->add($rule, $transaction, _w('Email not sent'), 'notice');
                    }
                } catch (Exception $ex) {
                    self::getLog()->add($rule, $transaction, _w('Email not sent:').' '.$ex->getMessage(), 'error');
                }
            }
        } else {
            self::getLog()->add($rule, $transaction, _w('Shop-Script order ID is unknown'), 'notice');
        }

        return !!$result;
    }

    /**
     * @param string $code
     * @return string
     */
    private static function getScript($code = '')
    {
        $script = '';
        switch ($code) {
            case 'self_update':
                $amount = _w('Amount');
                $hint_fix = _w('Set fix amount in the account currency.');
                $hint_percent = _w('Calculate based on the original transaction amount.');
                $script = <<<SCRIPT
let contractor_type = $(this).find('.contractor_type').val();

$(this).find('.property_contractor_id').addClass('hidden');
if (contractor_type == 'contractor_id') {
    $(this).find('.property_contractor_id').removeClass('hidden');
}

$(this).find('.amount_type').on('change', function() {
    let div_block = $(this).closest('div.value');
    let amount_type = $(this).val();
    
    div_block.find('p.hint').html('');
    div_block.find('.span-desc').remove();
    div_block.find('.property_amount').removeClass('hidden');
    if (amount_type == 'amount_percent') {
        div_block.find('.property_amount').before('<span class="span-desc">$amount * </span>');
        div_block.find('.property_amount').after('<span class="span-desc">%</span>');
        div_block.find('p.hint').html('$hint_percent');
    } else if (amount_type == 'amount_fix') {
        div_block.find('p.hint').html('$hint_fix');
    } else if (amount_type == 'amount_not_touch') {
        div_block.find('.property_amount').addClass('hidden');
    }
});
SCRIPT;
                break;
            case 'description':
                $script = <<<SCRIPT
let description_condition = $(this).find('.description[name$="[value]"]').val();

$(this).find('[name$="[custom_text]"]').addClass('hidden');
if (description_condition == 'custom_text') {
    $(this).find('[name$="[custom_text]"]').removeClass('hidden');
}
SCRIPT;
                break;
            case 'date_transaction':
                $script = <<<SCRIPT
let condition_id = $(this).data('condition-id');
let date_condition = $(this).find('.date_transaction[name$="[conditions]['+ condition_id +'][operator]"]').val();

if (!$(this).find('.date_transaction[name$="[conditions]['+ condition_id +'][value]"]').prop('disabled')) {
    $(this).find('.date_transaction[name$="[conditions]['+ condition_id +'][value]"]').removeClass('hidden');
    if (['is_today', 'is_future', 'is_past'].includes(date_condition)) {
        $(this).find('.date_transaction[name$="[conditions]['+ condition_id +'][value]"]').addClass('hidden');
   }
}
SCRIPT;
                break;
        }

        return $script;
    }
}
