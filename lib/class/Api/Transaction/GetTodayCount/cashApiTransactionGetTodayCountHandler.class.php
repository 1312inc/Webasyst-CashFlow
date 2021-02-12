<?php

/**
 * Class cashApiTransactionGetTodayCountHandler
 */
class cashApiTransactionGetTodayCountHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionGetTodayCountRequest $request
     *
     * @return cashApiTransactionGetTodayCountDto
     * @throws waException
     */
    public function handle($request)
    {
        $transactionRepository = cash()->getEntityRepository(cashTransaction::class);

        return new cashApiTransactionGetTodayCountDto(
            $request->today,
            $transactionRepository->countOnTodayBeforeDate($request->today, wa()->getUser()),
            $transactionRepository->countOnDate($request->today, wa()->getUser())
        );
    }
}
