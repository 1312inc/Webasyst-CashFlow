<?php

/**
 * Class cashApiTransactionGetListHandler
 */
class cashApiTransactionGetListHandler implements cashApiHandlerInterface
{
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
            $request->account_id,
            $request->category_id,
            $request->create_contact_id,
            $request->contractor_contact_id,
            $request->import_id,
            DateTime::createFromFormat('Y-m-d', $request->from),
            DateTime::createFromFormat('Y-m-d', $request->to),
            wa()->getUser(),
            $request->start,
            $request->limit
        );

        $data = (new cashTransactionFilterService())->getResults($filterDto);

        $initialBalance = null;
        if ($filterDto->accountId && cash()->getContactRights()->hasFullAccessToAccount($filterDto->contact, $filterDto->accountId)) {
            $initialBalance = cash()->getModel(cashAccount::class)->getStatDataForAccounts(
                '1970-01-01 00:00:00',
                $filterDto->endDate->format('Y-m-d 23:59:59'),
                $filterDto->contact,
                [$filterDto->accountId]
            );
            $initialBalance = (float) ifset($initialBalance, $filterDto->accountId, 'summary', 0.0);
        }

        $response = [];
        $iterator = cashApiTransactionResponseDtoAssembler::fromModelIteratorWithInitialBalance($data, $initialBalance, $filterDto->reverse);
        foreach ($iterator as $item) {
            $response[] = $item;
        }

        return $response;
    }
}
