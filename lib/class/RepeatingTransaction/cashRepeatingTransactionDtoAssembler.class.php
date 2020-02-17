<?php

/**
 * Class cashRepeatingTransactionDtoAssembler
 */
class cashRepeatingTransactionDtoAssembler
{
    /**
     * @param cashRepeatingTransaction $transaction
     * @param cashTransaction|null     $sourceTransaction
     *
     * @return cashRepeatingTransactionDto
     * @throws waException
     */
    public function createFromEntity(cashRepeatingTransaction $transaction, cashTransaction $sourceTransaction = null)
    {
        /** @var cashAccountDto $accountDto */
        $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $transaction->getAccount());
        /** @var cashCategoryDto $categoryDto */
        $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $transaction->getCategory());

        /** @var cashRepeatingTransactionDto $transactionDto */
        $transactionDto = cashDtoFromEntityFactory::fromEntity(cashRepeatingTransactionDto::class, $transaction);
        $transactionDto->account = $accountDto;
        $transactionDto->currency = $accountDto->currency;
        $transactionDto->category = $categoryDto;

        if ($sourceTransaction) {
            /** @var cashTransactionModel $model */
            $model = cash()->getModel(cashTransaction::class);
            $transactionDto->occurrences_in_future = $model->countRepeatingTransactionsFromDate(
                $transaction->getId(),
                $sourceTransaction->getDate()
            );
        }

        return $transactionDto;
    }
}
