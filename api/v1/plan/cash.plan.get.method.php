<?php

/**
 * Class cashPlanGetMethod
 */
class cashPlanGetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run(): cashApiResponseInterface
    {
        $model = cash()->getModel('cashPlan');
        $plans = $model->getAll();

        return new cashApiPlanGetResponse($plans);
    }
}
