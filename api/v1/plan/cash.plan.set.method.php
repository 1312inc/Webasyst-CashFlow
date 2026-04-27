<?php

/**
 * Class cashPlanSetMethod
 */
class cashPlanSetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    public function run(): cashApiResponseInterface
    {
        if (!cashHelper::isPremium()) {
            return new cashApiErrorResponse('payment_required', 'Payment premium version required', 402);
        }

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
        $category = $repository->findById($request->category_id);
        if (empty($category)) {
            return new cashApiErrorResponse('invalid_param', 'Unknown category');
        }

        $request->amount = abs($request->amount);
        if (cashCategory::TYPE_EXPENSE === $category->getType()) {
            $request->amount = -$request->amount;
        }

        $plan = (new cashApiPlanSetHandler())->handle($request);

        return new cashApiPlanSetResponse($plan);
    }
}
