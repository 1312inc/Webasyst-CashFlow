<?php

/**
 * cashApiPlanSetHandler
 */
class cashApiPlanSetHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiPlanSetRequest $request
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    public function handle($request)
    {
        $data = [
            'currency'    => $request->currency,
            'account_id'  => $request->account_id,
            'category_id' => $request->category_id,
            'month'       => ($request->date ? date('Y-m', strtotime($request->date)).'-01' : null),
        ];

        $model = cash()->getModel('cashPlan');

        if (empty($request->amount)) {
            $model->deleteByField($data);
            return [];
        } elseif ($plan = $model->getByField($data)) {
            $model->updateByField($plan, ['amount' => $request->amount]);
        } else {
            $data['amount'] = $request->amount;
            $id = $model->insert($data);
            if (!$id) {
                throw new kmwaNotFoundException(_w('Plan has not been saved'));
            }
        }

        $plans = (new cashApiPlanGetHandler)->handle($request);
        $plan = array_shift($plans);
        if (
            ($request->date && empty($plan['from']))
            || (!$request->date && !empty($plan['from']))
        ) {
            $plan = array_shift($plans);
        }

        return $plan;
    }
}
