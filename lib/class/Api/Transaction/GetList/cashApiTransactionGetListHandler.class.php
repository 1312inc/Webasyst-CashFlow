<?php

final class cashApiTransactionGetListHandler implements cashApiHandlerInterface
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
     * @return array<cashApiTransactionResponseDto>
     *
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        $filterDto = new cashTransactionFilterParamsDto(
            cashAggregateFilter::createFromHash($request->getFilter()),
            $request->getFrom(),
            $request->getTo(),
            wa()->getUser(),
            $request->getOffset(),
            $request->getLimit(),
            true,
            $request->getReverse()
        );

        $transactionFilter = new cashTransactionFilterService();

        if ($filterDto->filter->getTrash() !== null && !wa()->getUser()->isAdmin(cashConfig::APP_ID)) {
            throw new kmwaForbiddenException('Trash only for admins');
        }

        $total = $transactionFilter->getResultsCount($filterDto);
        /** @var Iterator $data */
        $data = $transactionFilter->getResults($filterDto);

        $lastTransaction = $data->current();

        $initialBalance = null;
        if ($filterDto->filter->getAccountId()
            && cash()->getContactRights()->hasFullAccessToAccount(
                $filterDto->contact,
                $filterDto->filter->getAccountId()
            )
        ) {
            $endDate = $filterDto->endDate->format('Y-m-d 23:59:59');
            $endDate = $lastTransaction ?  min($lastTransaction['date'] . ' 23:59:59', $endDate) : $endDate;
            $initialBalance = cash()->getModel(cashAccount::class)
                ->getStatDataForAccounts(
                    '1970-01-01 00:00:00',
                    $endDate,
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
