<?php

/**
 * Class cashPlanGetMethod
 */
class cashPlanGetMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    public function run(): cashApiResponseInterface
    {
        /** @var cashApiPlanGetRequest $request */
        $request = $this->fillRequestWithParams(new cashApiPlanGetRequest());

        $plans = (new cashApiPlanGetHandler())->handle($request);

        return new cashApiPlanGetResponse($plans);
    }
}
