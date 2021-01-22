<?php

/**
 * Class cashApiTransactionGetBadgeCountHandler
 */
class cashApiTransactionGetBadgeCountHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionGetBadgeCountRequest $request
     *
     * @return cashApiTransactionGetBadgeCountDto
     * @throws waException
     */
    public function handle($request)
    {
        $transactionRepository = cash()->getEntityRepository(cashTransaction::class);

        return new cashApiTransactionGetBadgeCountDto(
            $request->date,
            $transactionRepository->countOnBadgeBeforeDate($request->date, wa()->getUser())
        );
    }
}
