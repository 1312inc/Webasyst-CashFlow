<?php

/**
 * cashApiPlanGetHandler
 */
class cashApiPlanGetHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiPlanGetRequest|cashApiPlanSetRequest $request
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    public function handle($request)
    {
        $model = cash()->getModel('cashPlan');
        $where = ['1=1'];

        if (isset($request->currency)) {
            $where[] = 'cp.currency = s:currency';
        }
        if (isset($request->category_id)) {
            $where[] = 'cp.category_id = i:category_id';
        }
        if (isset($request->account_id)) {
            $where[] = '(cp.account_id = i:account_id)';
        }
        if (empty($request->date)) {
            $where[] = 'cp.`month` IS NULL';
            return $model->query("
                SELECT cp.id, cp.currency, cp.account_id, cp.category_id, NULL `from`, NULL `to`, cp.amount, NULL amount_fact             
                FROM cash_plan cp
                LEFT JOIN cash_account ca ON ca.currency = cp.currency
                WHERE ".implode(' AND ', $where)."
                GROUP BY cp.id, cp.currency, cp.account_id, cp.category_id, cp.amount 
                ORDER BY cp.currency, cp.account_id, cp.category_id
            ", [
                'currency'    => $request->currency,
                'category_id' => $request->category_id,
                'account_id'  => $request->account_id,
            ])->fetchAll();
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d|', $request->date);
        $date_from = $date->modify('first day of this month')->format('Y-m-d');
        $date_to = $date->modify('last day of this month')->format('Y-m-d');
        $where[] = '(cp.`month` IS NULL OR (cp.`month` >= @date_from AND cp.`month` < DATE_ADD(@date_to, INTERVAL 1 DAY)))';

        $model->exec('SET @date_from:= '.($date_from ? "'$date_from'" : 'NULL'));
        $model->exec('SET @date_to:= '.($date_to ? "'$date_to'" : 'NULL'));

        return $model->query("
            SELECT cp.id, cp.currency, cp.account_id, cp.category_id, IF(cp.`month` IS NULL, cp.`month`, @date_from) `from`, IF(cp.`month` IS NULL, cp.`month`, @date_to) `to`, cp.amount, IF(@date_to IS NULL, NULL, SUM(IF(ct.amount, ct.amount, 0))) amount_fact             
            FROM cash_plan cp
            LEFT JOIN cash_account ca ON ca.currency = cp.currency
            LEFT JOIN cash_transaction ct ON ca.id = ct.account_id AND ct.category_id = cp.category_id 
            AND CASE
                WHEN ca.is_imaginary = 1 THEN ct.date > NOW()
                WHEN ca.is_imaginary = -1 THEN NULL
                ELSE ca.is_imaginary = 0
            END
            WHERE ".implode(' AND ', $where)."
            GROUP BY cp.id, cp.currency, cp.account_id, cp.category_id, cp.amount 
            ORDER BY cp.currency, cp.account_id, cp.category_id
        ", [
            'currency'    => $request->currency,
            'category_id' => $request->category_id,
            'account_id'  => $request->account_id,
        ])->fetchAll();
    }
}
