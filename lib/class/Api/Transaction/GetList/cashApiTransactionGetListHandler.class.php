<?php

/**
 * Class cashApiTransactionGetListHandler
 */
class cashApiTransactionGetListHandler implements cashApiHandlerInterface
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
     * @param cashApiTransactionGetListRequest $request
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
            $request->offset,
            $request->limit
        );

        $transactionFilter = new cashTransactionFilterService();

        $total = $transactionFilter->getResultsCount($filterDto);
        $data = $transactionFilter->getResults($filterDto);

        $initialBalance = null;
        if ($filterDto->filter->getAccountId()
            && cash()->getContactRights()->hasFullAccessToAccount(
                $filterDto->contact,
                $filterDto->filter->getAccountId()
            )
        ) {
            $initialBalance = cash()->getModel(cashAccount::class)->getStatDataForAccounts(
                '1970-01-01 00:00:00',
                $filterDto->endDate->format('Y-m-d 23:59:59'),
                $filterDto->contact,
                [$filterDto->filter->getAccountId()]
            );
            $initialBalance = (float) ifset($initialBalance, $filterDto->filter->getAccountId(), 'summary', 0.0);
        }

        $response = [];
        $iterator = $this->transactionResponseDtoAssembler->fromModelIteratorWithInitialBalance(
            $data,
            $initialBalance,
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
