<?php

/**
 * Class cashApiTransactionGetShrinkListHandler
 */
class cashApiTransactionGetShrinkListHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionGetShrinkListRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     * @throws waException
     */
    public function handle($request)
    {
        $filterDto = new cashTransactionFilterParamsDto(
            cashAggregateFilter::createFromHash($request->filter),
            DateTime::createFromFormat('Y-m-d', $request->from),
            DateTime::createFromFormat('Y-m-d', $request->to),
            wa()->getUser(),
            0,
            13
        );

        $transactionFilter = new cashTransactionFilterService();

        $data = $transactionFilter->getShrinkResults($filterDto);

        $response = [];
        $iterator = cashApiShrinkTransactionResponseDtoAssembler::fromModelIterator($data);
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return $response;
    }
}
