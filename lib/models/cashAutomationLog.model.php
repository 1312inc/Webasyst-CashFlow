<?php

/**
 * Class cashAutomationLogModel
 */
class cashAutomationLogModel extends cashModel
{
    protected $table = 'cash_automation_log';

    const TYPE = [
        'normal',
        'notice',
        'warning',
        'error'
    ];

    /**
     * @param array $rule
     * @param array $transaction
     * @param string $description
     * @param string $type
     * @return void
     */
    public function add($rule, $transaction, $description = '', $type = 'normal')
    {
        $this->insert([
            'datetime' => date('Y-m-d H:i:s'),
            'automation_id' => $rule['id'],
            'transaction_id' => $transaction['id'],
            'type' => in_array($type, self::TYPE) ? $type : 'normal',
            'plugin_id' => ifset($rule, 'rule_data', 'plugin_id', null),
            'automation_action' => $rule['action_id'],
            'description' => $description,
            'automation_rule_json' => waUtils::jsonEncode($rule, JSON_UNESCAPED_UNICODE),
            'transaction_json' => waUtils::jsonEncode($transaction, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * @param int $automation_id
     * @param int $limit
     * @return array
     */
    public function getLogs(int $automation_id, int $limit = 5): array
    {
        return $this->select('datetime, automation_id, transaction_id, type, plugin_id, automation_action, description')
            ->where('automation_id = ?', $automation_id)
            ->order('datetime DESC')
            ->limit($limit)
            ->fetchAll();
    }
}
