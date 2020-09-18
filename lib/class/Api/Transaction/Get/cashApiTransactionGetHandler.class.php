<?php

/**
 * Class cashApiTransactionGetHandler
 */
class cashApiTransactionGetHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionGetRequest $request
     *
     * @return cashApiTransactionGetResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($request->id);
        if (!$transaction) {
            throw new kmwaNotFoundException(_w('No transaction'));
        }

        if (!cash()->getContactRights()->canEditOrDeleteTransaction(wa()->getUser(), $transaction)) {
            throw new kmwaForbiddenException(_w('You can view this transaction'));
        }

        $data = cash()->getHydrator()->extract($transaction);

        $dto = new cashApiTransactionGetResponseDto($data);

        $repeatingTransaction = $transaction->getRepeatingTransaction();
        if ($repeatingTransaction) {
            $dto->occurrences_in_future = cash()->getModel(cashTransaction::class)->countRepeatingTransactionsFromDate(
                $repeatingTransaction->getId(),
                $transaction->getDate()
            );
            $dto->repeating_interval = $repeatingTransaction->getRepeatingInterval();
            $dto->repeating_end_type = $repeatingTransaction->getRepeatingEndType();
            $dto->repeating_end_conditions = $repeatingTransaction->getRepeatingEndConditions();
            $dto->repeating_frequency = (int) $repeatingTransaction->getRepeatingFrequency();
        }

        return $dto;
    }
}
