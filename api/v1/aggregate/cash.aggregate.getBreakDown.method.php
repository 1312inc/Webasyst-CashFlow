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
        $request->children_help_parents = empty($request->children_help_parents) ? 0 : 1;

        [$data, $currencies] = (new cashApiAggregateGetBreakDownHandler())->handle($request);

        return new cashApiAggregateGetBreakDownResponse((array) $data, (array) $currencies, $request->children_help_parents);
    }
}
