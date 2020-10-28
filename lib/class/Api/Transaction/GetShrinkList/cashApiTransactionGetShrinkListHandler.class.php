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
     * @throws kmwaForbiddenException
     */
    public function handle($request)
    {
        $filterDto = new cashTransactionFilterParamsDto(
            cashAggregateFilter::createFromHash($request->filter),
            DateTime::createFromFormat('Y-m-d', $request->from),
            DateTime::createFromFormat('Y-m-d', $request->to),
            wa()->getUser(),
            $request->offset * $request->limit,
            $request->limit
        );

        $transactionFilter = new cashTransactionFilterService();

        $total = $transactionFilter->getShrinkResultsCount($filterDto);
        $data = $transactionFilter->getShrinkResults($filterDto);

        $response = [];
        $iterator = cashApiTransactionResponseDtoAssembler::fromModelIteratorWithInitialBalance(
            $data,
            0.0,
            $filterDto->reverse
        );
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return [
            'data' => $response,
            'total' => $total,
        ];
    }
}
