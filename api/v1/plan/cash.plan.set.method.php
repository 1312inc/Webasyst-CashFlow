<?php

/**
 * Class cashPlanSetMethod
 */
class cashPlanSetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    public function run(): cashApiResponseInterface
    {
        /** @var cashApiPlanSetRequest $request */
        $request = $this->fillRequestWithParams(new cashApiPlanSetRequest());

        $errors = [];
        if (!isset($request->amount)) {
            $errors[] = 'amount';
        }
        if (empty($request->category_id)) {
            $errors[] = 'category_id';
        }
        if (empty($request->currency)) {
            $errors[] = 'currency';
        }
        if (!empty($errors)) {
            return new cashApiErrorResponse('invalid_param', 'Required parameter is missing: '.implode(', ', $errors));
        }

        $currency_info = waCurrency::getInfo($request->currency);
        if (empty($currency_info)) {
            return new cashApiErrorResponse('invalid_param', 'Unknown currency');
        }

        /** @var cashCategoryRepository $repository */
        $repository = cash()->getEntityRepository(cashCategory::class);
        if (!$repository->findById($request->category_id)) {
            return new cashApiErrorResponse('invalid_param', 'Unknown category');
        }

        $plan = (new cashApiPlanSetHandler())->handle($request);

        return new cashApiPlanSetResponse($plan);
    }
}
