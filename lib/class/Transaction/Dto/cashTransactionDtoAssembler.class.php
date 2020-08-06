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
     * @param float|null         $initialBalance
     *
     * @return Generator
     */
    public function generateFromIterator(
        waDbResultIterator $data,
        array $accounts,
        array $categories,
        $initialBalance = null
    ) {
        foreach ($data as $datum) {
            if ($initialBalance !== null && !isset($datum['balance'])) {
                $datum['balance'] = $initialBalance;
            }

            $dto = new cashTransactionDto(
                $datum,
                $accounts[$datum['account_id']],
                $accounts[$datum['account_id']]->currency,
                ifset($categories, $datum['category_id'], null)
            );

            if ($initialBalance !== null) {
                $initialBalance -= $datum['amount'];
            }

            if ($datum['external_hash']) {
                $dto->external_entity = cashTransactionExternalEntityFactory::createFromSource(
                    $datum['external_source'], $datum['external_data']
                );
            }

            yield $datum['id'] => $dto;
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

        if ($transaction->getExternalHash()) {
            $transactionDto->external_entity = cashTransactionExternalEntityFactory::createFromTransaction(
                $transaction
            );
        }

        return $transactionDto;
    }
}
