<?php

/**
 * cashApiPlanGetHandler
 */
class cashApiPlanGetHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiPlanGetRequest $request
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    public function handle($request)
    {
        $model = cash()->getModel('cashPlan');
        $where = ['(cp.`month` IS NULL OR cp.`month` BETWEEN s:date_from AND s:date_to)'];
        if (isset($request->currency)) {
            $where[] = 'cp.currency = s:currency';
        }
        if (isset($request->category_id)) {
            $where[] = 'cp.category_id = i:category_id';
        }
        if (isset($request->account_id)) {
            $where[] = '(cp.account_id = i:account_id OR cp.account_id IS NULL)';
        }

        return $model->query("
            SELECT cp.id, cp.currency, cp.account_id, cp.category_id, s:date_from `from`, s:date_to `to`, cp.amount, SUM(IF(ct.amount, ct.amount, 0)) amount_fact
            FROM cash_plan cp 
            LEFT JOIN cash_transaction ct ON ct.category_id = cp.category_id AND ct.`date` BETWEEN s:date_from AND s:date_to
            LEFT JOIN cash_account ca ON ca.id = ct.account_id AND ca.currency = cp.currency 
            WHERE ".implode(' AND ', $where)."
            GROUP BY cp.id, cp.currency, cp.account_id, cp.category_id, cp.amount 
            ORDER BY cp.currency, cp.account_id, cp.category_id
        ", [
            'date_from'   => $request->date->modify('first day of this month')->format('Y-m-d'),
            'date_to'     => $request->date->modify('last day of this month')->format('Y-m-d'),
            'currency'    => $request->currency,
            'category_id' => $request->category_id,
            'account_id'  => $request->account_id
        ])->fetchAll();
    }
}
