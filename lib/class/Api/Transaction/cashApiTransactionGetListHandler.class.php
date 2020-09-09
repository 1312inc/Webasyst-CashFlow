<?php

/**
 * Class cashApiTransactionsGetListHandler
 */
class cashApiTransactionGetListHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiTransactionGetListRequest $request
     *
     * @return array|cashApiTransactionDto[]
     * @throws waException
     */
    public function handle($request): array
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $startDate = DateTime::createFromFormat('Y-m-d', $request->from);
        $endDate = DateTime::createFromFormat('Y-m-d', $request->to);

        $data = $model->getByDateBoundsAndAccount(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59'),
            wa()->getUser(),
            null,
            false
        );

        $dtos = [];
        foreach ($this->generateResponse($data) as $item) {
            $dtos[] = $item;
        }

        return $dtos;
    }

    /**
     * @param Iterator $transactionData
     *
     * @return Generator
     */
    private function generateResponse(Iterator $transactionData)
    {
        foreach ($transactionData as $transactionDatum) {
            yield new cashApiTransactionDto($transactionDatum);
        }
    }
}