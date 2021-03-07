<?php

/**
 * Class cashAggregateGetBalanceOnDateMethod
 */
final class cashAggregateGetBalanceOnDateMethod extends cashApiAbstractMethod
{
    protected $method = self::METHOD_GET;

    /**
     * @return cashApiAccountCreateResponse
     * @throws waAPIException
     * @throws waException
     */
    public function run(): cashApiResponseInterface
    {
        /** @var cashApiAggregateGetBalanceOnDateRequest $request */
        $request = $this->fillRequestWithParams(new cashApiAggregateGetBalanceOnDateRequest());
        $request->date = DateTimeImmutable::createFromFormat('Y-m-d|', $request->date);

        $response = (new cashApiAggregateGetBalanceOnDateHandler())->handle($request);

        return new cashApiAggregateGetBalanceOnDateResponse($response);
    }
}
