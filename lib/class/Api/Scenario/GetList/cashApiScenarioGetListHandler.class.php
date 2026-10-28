<?php

/**
 * cashApiScenarioGetListHandler
 */
class cashApiScenarioGetListHandler implements cashApiHandlerInterface
{
    public function handle($request)
    {
        return $this->getStatDataForScenarios();
    }

    public function getStatDataForScenarios()
    {
        return cash()->getModel('cashScenario')->query("         
            SELECT cs.*, IF(cn.count, cn.count, 0) count_transaction FROM cash_scenario cs 
            LEFT JOIN (
                SELECT cs.id, IFNULL(COUNT(ct.id), 0) AS count
                FROM cash_scenario cs
                LEFT JOIN cash_transaction ct ON ct.scenario_id = cs.id
                LEFT JOIN cash_account ca ON ca.id = ct.account_id
                WHERE ca.is_archived = 0 AND ct.is_archived = 0 AND ct.date <= s:now
                GROUP BY cs.id
            ) cn ON cn.id = cs.id 
            ORDER BY cs.sort ASC, cs.id DESC
        ", [
            'now' => date('Y-m-d'),
        ])->fetchAll();
    }
}
