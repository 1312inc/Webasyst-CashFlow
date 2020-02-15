<?php

/**
 * Class cashRepeatingTransactionDtoAssembler
 */
class cashRepeatingTransactionDtoAssembler
{
    /**
     * @param cashRepeatingTransaction $transaction
     *
     * @return cashRepeatingTransactionDto
     * @throws waException
     */
    public function createFromEntity(cashRepeatingTransaction $transaction)
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

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $transactionDto->occurrences_in_future = $model->countRepeatingTransactionsFromDate(
            $transaction->getId(),
            date('Y-m-d H:i:s')
        );

        return $transactionDto;
    }
}
