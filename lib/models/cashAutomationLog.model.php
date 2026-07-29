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
            'automation_event' => $rule['action_id'],
            'automation_action' => ifset($rule, 'rule_data', 'action', null),
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
        $logs = $this->select('id, datetime, automation_id, transaction_id, type, plugin_id, automation_event, automation_action, description, automation_rule_json automation_rule, transaction_json transaction')
            ->where('automation_id = ?', $automation_id)
            ->order('datetime DESC')
            ->limit($limit)
            ->fetchAll();
        foreach ($logs as &$_log) {
            $_log['automation_rule'] = waUtils::jsonDecode($_log['automation_rule'], true);
            $_log['transaction'] = waUtils::jsonDecode($_log['transaction'], true);
        }
        unset($_log);

        return $logs;
    }

    /**
     * @param array $automation_ids
     * @return array
     * @throws waDbException
     */
    public function getInfoLogs(array $automation_ids): array
    {
        return $this->query("
            SELECT automation_id, MAX(`datetime`) last_date_log, COUNT(id) count_log FROM cash_automation_log
            WHERE automation_id IN (:automation_ids)
            GROUP BY automation_id
            ORDER BY automation_id DESC
        ", [
            'automation_ids' => $automation_ids
        ])->fetchAll('automation_id');
    }
}
