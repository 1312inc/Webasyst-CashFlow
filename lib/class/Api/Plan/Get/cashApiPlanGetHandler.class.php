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
        $where = ['1=1'];
        $total_facts = [];
        $date_from = null;
        $date_to = null;
        $model = cash()->getModel('cashPlan');
        if ($request->date) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d|', $request->date);
            $date_from = $date->modify('first day of this month')->format('Y-m-d');
            $date_to = $date->modify('last day of this month')->format('Y-m-d');

            $_where = [
                'ca.is_archived != 1',
                'ct.is_archived != 1',
                'IF(ca.is_imaginary = -1, NULL, true)',
                'ct.date >= s:date_from AND ct.date < DATE_ADD(s:date_to, INTERVAL 1 DAY)'
            ];
            $total_facts = $model->query("
                SELECT ct.account_id, ca.currency, ct.category_id, cc.category_parent_id, SUM(ct.amount) amount_fact
                FROM cash_transaction ct
                LEFT JOIN cash_account ca ON ca.id = ct.account_id
                LEFT JOIN cash_category cc ON cc.id = ct.category_id 
                WHERE ".implode(' AND ', $_where)."
                GROUP BY ct.account_id, ct.category_id
                ORDER BY ca.id, ca.currency, cc.id
            ", [
                'date_from' => $date_from,
                'date_to'   => $date_to,
            ])->fetchAll();
            $where['month'] = "(`month` IS NULL OR `month` = '$date_from')";
        } else {
            $where['month'] = '`month` IS NULL';
        }

        if (isset($request->currency)) {
            $where['currency'] = 'currency = s:currency';
        }
        if (isset($request->category_id)) {
            $where['category_id'] = 'category_id = i:category_id';
        }
        if (isset($request->account_id)) {
            $where['account_id'] = 'account_id = i:account_id';
        }

        $plans = $model->query("
            SELECT *, NULL `from`, NULL `to`, NULL amount_fact
            FROM cash_plan
            WHERE ".implode(' AND ', $where)."
            ORDER BY currency, account_id, category_id
        ", [
            'currency'    => $request->currency,
            'category_id' => $request->category_id,
            'account_id'  => $request->account_id,
        ])->fetchAll();

        foreach ($plans as &$plan) {
            $plan['from'] = (is_null($plan['month']) ? null : $date_from);
            $plan['to'] = (is_null($plan['month']) ? null : $date_to);

            if (is_null($plan['month'])) {
                /** для общего типа плана */
                $plan['amount_fact'] = null;
            } else {
                foreach ($total_facts as $_fact) {
                    $is_currency_type = $plan['currency'] == $_fact['currency'] && $plan['category_id'] == $_fact['category_id'];
                    if ($is_currency_type && $plan['account_id'] == $_fact['account_id']) {
                        /** для месячного плана конкретного счета */
                        $plan['amount_fact'] = $_fact['amount_fact'];
                        break;
                    } elseif ($is_currency_type) {
                        /** для месячного плана конкретного счета */
                        $plan['amount_fact'] += $_fact['amount_fact'];
                    }

                    if (ifempty($_fact, 'category_parent_id', null) == $plan['category_id']) {
                        $plan['amount_fact'] += $_fact['amount_fact'];
                    }
                }
            }
        }
        unset($plan);

        return $plans;
    }
}
