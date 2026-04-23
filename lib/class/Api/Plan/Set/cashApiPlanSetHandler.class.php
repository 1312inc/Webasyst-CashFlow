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
        $plan = [
            'currency'    => $request->currency,
            'account_id'  => $request->account_id,
            'category_id' => $request->category_id,
            'month'       => ($request->date ? date('Y-m', strtotime($request->date)).'-01' : null),
            'amount'      => $request->amount,
        ];

        $model = cash()->getModel('cashPlan');
        if (empty($request->id)) {
            $request->id = $model->insert($plan);
        } elseif (empty($request->amount)) {
            $model->deleteById($request->id);
            return [];
        } else {
            $model->updateById($request->id, $plan);
        }

        $plans = (new cashApiPlanGetHandler)->handle($request);

        return reset($plans);
    }
}
