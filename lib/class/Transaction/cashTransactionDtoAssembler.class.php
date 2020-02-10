<?php

/**
 * Class cashTransactionDtoAssembler
 */
class cashTransactionDtoAssembler
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int|null    $account
     *
     * @return array
     * @throws waException
     */
    public function findByDatesAndAccount(DateTime $startDate, DateTime $endDate, $account = null)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $accountDtos = [];
        foreach (cash()->getEntityRepository(cashAccount::class)->findAllActive() as $a) {
            $accountDtos[$a->getId()] = cashAccountDto::fromEntity($a);
        }

        $data = $model->getByDateBoundsAndAccount(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59'),
            $account
        );

        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAll()
        );

        $dtos = [];
        foreach ($this->generateFromIterator($data, $accountDtos, $categoryDtos) as $id => $dto) {
            $dtos[$id] = $dto;
        }

        return $dtos;
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int|null $category
     *
     * @return array
     * @throws waException
     */
    public function findByDatesAndCategory(DateTime $startDate, DateTime $endDate, $category = null)
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $accountDtos = [];
        foreach (cash()->getEntityRepository(cashAccount::class)->findAllActive() as $a) {
            $accountDtos[$a->getId()] = cashDtoFromEntityFactory::fromEntity(cashAccountDto::class, $a);
            $accountDtos[$a->getId()]->currency = cashCurrencyVO::fromWaCurrency($a->getCurrency());
        }

        $data = $model->getByDateBoundsAndCategory(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59'),
            $category
        );

        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAll()
        );

        $dtos = [];
        foreach ($this->generateFromIterator($data, $accountDtos, $categoryDtos) as $id => $dto) {
            $dtos[$id] = $dto;
        }

        return $dtos;
    }

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
