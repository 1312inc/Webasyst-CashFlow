<?php

final class cashApiTransactionGetShrinkListHandler implements cashApiHandlerInterface
{
    private const SHRINK_LIMIT = 13;

    /**
     * @var cashApiShrinkTransactionResponseDtoAssembler
     */
    private $shrinkTransactionResponseDtoAssembler;

    public function __construct()
    {
        $this->shrinkTransactionResponseDtoAssembler = new cashApiShrinkTransactionResponseDtoAssembler();
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
        $iterator = $this->shrinkTransactionResponseDtoAssembler->fromModelIterator($data);
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return $response;
    }
}
