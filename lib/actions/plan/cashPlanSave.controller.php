<?php

class cashPlanSaveController extends cashJsonController
{
    public function execute()
    {
        $id = waRequest::post('id', null, waRequest::TYPE_INT);
        $category_id = waRequest::post('category_id', null, waRequest::TYPE_INT);
        $num_month = waRequest::post('month', null, waRequest::TYPE_INT);
        $currency = waRequest::post('currency', null, waRequest::TYPE_STRING_TRIM);
        $amount = (float) waRequest::post('amount', 0, waRequest::TYPE_STRING_TRIM);

        $plan = [
            'currency'    => $currency,
            'account_id'  => null,
            'category_id' => $category_id,
            'month'       => $num_month ? date(sprintf('Y-%02d-01', $num_month)) : null,
            'amount'      => $amount
        ];
        $model = cash()->getModel('cashPlan');
        if ($id > 0) {
            $model->updateById($id, $plan);
        } else {
            $model->insert($plan);
        }
    }
}
