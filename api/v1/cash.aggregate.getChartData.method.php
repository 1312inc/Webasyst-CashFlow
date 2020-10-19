<?php

/**
 * Class cashAggregateGetChartDataMethod
 */
class cashAggregateGetChartDataMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_POST;

    /**
     * @return cashApiAccountCreateResponse
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waAPIException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAggregateGetChartDataRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAggregateGetChartDataRequest());
        $request->to = DateTimeImmutable::createFromFormat('Y-m-d', $request->to);
        $request->from = DateTimeImmutable::createFromFormat('Y-m-d', $request->from);
        
        $response = (new cashApiAggregateGetChartDataHandler())->handle($request);

        return new cashApiAggregateGetChartDataResponse($response);
    }
}
