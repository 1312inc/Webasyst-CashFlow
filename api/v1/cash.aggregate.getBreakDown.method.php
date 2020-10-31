<?php

/**
 * Class cashAggregateGetBreakDownMethod
 */
class cashAggregateGetBreakDownMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiAggregateGetBreakDownResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAggregateGetBreakDownRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAggregateGetBreakDownRequest());
        $request->to = DateTimeImmutable::createFromFormat('Y-m-d|', $request->to);
        $request->from = DateTimeImmutable::createFromFormat('Y-m-d|', $request->from);

        $response = (new cashApiAggregateGetBreakDownHandler())->handle($request);

        return new cashApiAggregateGetBreakDownResponse($response);
    }
}
