<?php

/**
 * Class cashApiTransactionGetUpNextListHandler
 */
class cashApiTransactionGetUpNextListHandler implements cashApiHandlerInterface
{
    /**
     * @var cashApiTransactionResponseDtoAssembler
     */
    private $transactionResponseDtoAssembler;

    public function __construct()
    {
        $this->transactionResponseDtoAssembler = new cashApiTransactionResponseDtoAssembler();
    }

    /**
     * @param cashApiTransactionGetUpNextListRequest $request
     *
     * @return array|cashApiTransactionResponseDto[]
     * @throws waException
     * @throws kmwaForbiddenException
     */
    public function handle($request)
    {
        $filterDto = new cashTransactionFilterParamsDto(
            cashAggregateFilter::createFromHash(null),
            $request->today,
            $request->today->modify('+30 days'),
            wa()->getUser()
        );

        $transactionFilter = new cashTransactionFilterService();

        $data = $transactionFilter->getUpNextResults($filterDto);

        $response = [];
        $iterator = $this->transactionResponseDtoAssembler->fromModelIteratorWithInitialBalance(
            $data,
            null,
            $filterDto->reverse
        );
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return [
            'data' => $response,
        ];
    }
}
