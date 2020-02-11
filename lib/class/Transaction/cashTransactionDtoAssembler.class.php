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
     * @throws waException
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
     * @throws kmwaAssertException
     * @throws waException
     */
    public function createFromEntity(cashTransaction $transaction)
    {
        if ($transaction->getAccountId()) {
            $account = cash()->getEntityRepository(cashAccount::class)->findById($transaction->getAccountId());
            kmwaAssert::instance($account, cashAccount::class);
            $accountDto = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $account);
        } else {
            $accountDto = new cashAccountDto();
        }

        if ($transaction->getCategoryId()) {
            $category = cash()->getEntityRepository(cashCategory::class)->findById($transaction->getCategoryId());
            kmwaAssert::instance($category, cashCategory::class);
            $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category);
        } else {
            $categoryDto = new cashCategoryDto();
        }

        /** @var cashTransactionDto $transactionDto */
        $transactionDto = cashDtoFromEntityFactory::fromEntity(cashTransactionDto::class, $transaction);
        $transactionDto->account = $accountDto;
        $transactionDto->currency = $accountDto->currency;
        $transactionDto->category = $categoryDto;

        return $transactionDto;
    }
}
