<?php

/**
 * Class cashApiAggregateGetBreakDownHandler
 */
final class cashApiAggregateGetBreakDownHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiAggregateGetBreakDownRequest $request
     *
     * @return cashApiAggregateGetBreakDownDataDto[]
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function handle($request)
    {
        $user = wa()->getUser();
        $paramsDto = new cashAggregateGetBreakDownFilterParamsDto(
            $user,
            $request->from,
            $request->to,
            cashAggregateFilter::createFromHash($request->filter),
            $request->company_id
        );

        $graphService = new cashGraphService();

        $data = $graphService->getAggregateBreakDownData($paramsDto, $request);
        $existingCurrencies = $graphService->getAggregateBreakDownCurrencies($paramsDto);

        return [$data, $existingCurrencies];
    }
}
