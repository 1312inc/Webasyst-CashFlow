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
        $request->date = DateTimeImmutable::createFromFormat('Y-m-d|', $request->date);

        $plans = (new cashApiPlanGetHandler())->handle($request);

        return new cashApiPlanGetResponse($plans);
    }
}
