<?php

final class cashApiTransactionGetShrinkListHandler implements cashApiHandlerInterface
{
    private const SHRINK_LIMIT = 13;

    /**
     * @var cashApiTransactionResponseDtoAssembler
     */
    private $transactionResponseDtoAssembler;

    public function __construct()
    {
        $this->transactionResponseDtoAssembler = new cashApiTransactionResponseDtoAssembler();
    }

    /**
     * @param cashApiTransactionGetShrinkListRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     *
     * @throws waException
     */
    public function handle($request)
    {
        $filterDto = new cashTransactionFilterParamsDto(
            cashAggregateFilter::createFromHash($request->getFilter()),
            $request->getFrom(),
            $request->getTo(),
            wa()->getUser(),
            0,
            self::SHRINK_LIMIT
        );

        $transactionFilter = new cashTransactionFilterService();

        $data = $transactionFilter->getShrinkResults($filterDto);

        $response = [];
        $iterator = $this->transactionResponseDtoAssembler->fromModelIterator($data);
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return $response;
    }
}
