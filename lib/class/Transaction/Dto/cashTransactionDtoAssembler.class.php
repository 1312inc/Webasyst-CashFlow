<?php

/**
 * Class cashTransactionDtoAssembler
 */
class cashTransactionDtoAssembler
{
    /**
     * @param waDbResultIterator $data
     * @param cashAccountDto[]   $accounts
     * @param cashCategoryDto[]  $categories
     *
     * @return Generator
     */
    public function generateFromIterator(waDbResultIterator $data, array $accounts, array $categories)
    {
        foreach ($data as $datum) {
            yield $datum['id'] => new cashTransactionDto(
                $datum,
                $accounts[$datum['account_id']],
                $accounts[$datum['account_id']]->currency,
                ifset($categories, $datum['category_id'], null)
            );
        }
    }

    /**
     * @param cashTransaction $transaction
     *
     * @return cashTransactionDto
     * @throws waException
     */
    public function createFromEntity(cashTransaction $transaction)
    {
        /** @var cashAccountDto $accountDto */
        $accountDto = $transaction->getAccount()
            ? cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $transaction->getAccount())
            : new cashAccountDto();
        /** @var cashCategoryDto $categoryDto */
        $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $transaction->getCategory());

        /** @var cashTransactionDto $transactionDto */
        $transactionDto = cashDtoFromEntityFactory::fromEntity(cashTransactionDto::class, $transaction);
        $transactionDto->account = $accountDto;
        $transactionDto->currency = $accountDto->currency;
        $transactionDto->category = $categoryDto;

        return $transactionDto;
    }
}
