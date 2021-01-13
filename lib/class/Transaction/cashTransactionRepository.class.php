<?php

/**
 * Class cashTransactionRepository
 *
 * @method cashTransactionModel getModel()
 */
class cashTransactionRepository extends cashBaseRepository
{
    protected $entity = cashTransaction::class;

    /**
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     * @param cashTransactionPageFilterDto $filterDto
     * @param cashPagination|null          $pagination
     *
     * @return array
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function findByDatesAndFilter(
        DateTime $startDate,
        DateTime $endDate,
        cashTransactionPageFilterDto $filterDto,
        cashPagination $pagination = null
    ): array {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $accountDtos = [];
        foreach (cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact($filterDto->contact) as $a) {
            $accountDtos[$a->getId()] = cashAccountDto::fromEntity($a);
        }

        $start = null;
        $limit = null;
        if ($pagination) {
            $start = $pagination->getStart();
            $limit = $pagination->getLimit();
        }

        $initialBalance = null;

        switch ($filterDto->type) {
            case cashTransactionPageFilterDto::FILTER_ACCOUNT:
                $data = $model->getByDateBoundsAndAccount(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $filterDto->contact,
                    $filterDto->id,
                    false,
                    $start,
                    $limit
                );

                if ($pagination) {
                    $pagination->setTotalRows(
                        $model->countByDateBoundsAndAccount(
                            $startDate->format('Y-m-d 00:00:00'),
                            $endDate->format('Y-m-d 23:59:59'),
                            $filterDto->contact,
                            $filterDto->id
                        )
                    );
                }

                if ($data->count() && $filterDto->id) {
                    $initialBalance = cash()->getModel(cashAccount::class)->getStatDataForAccounts(
                        '1970-01-01 00:00:00',
                        $endDate->format('Y-m-d 23:59:59'),
                        $filterDto->contact,
                        [$filterDto->id]
                    );
                    $initialBalance = (float) ifset($initialBalance, $filterDto->id, 'summary', 0.0);
                }
                break;

            case cashTransactionPageFilterDto::FILTER_CATEGORY:
                $data = $model->getByDateBoundsAndCategory(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $filterDto->contact,
                    $filterDto->id,
                    false,
                    $start,
                    $limit
                );

                if ($pagination) {
                    $pagination->setTotalRows(
                        $model->countByDateBoundsAndCategory(
                            $startDate->format('Y-m-d 00:00:00'),
                            $endDate->format('Y-m-d 23:59:59'),
                            $filterDto->contact,
                            $filterDto->id
                        )
                    );
                }
                break;

            case cashTransactionPageFilterDto::FILTER_IMPORT:
                $data = $model->getByDateBoundsAndImport(
                    $startDate->format('Y-m-d 00:00:00'),
                    $endDate->format('Y-m-d 23:59:59'),
                    $filterDto->contact,
                    $filterDto->id,
                    false,
                    $start,
                    $limit
                );

                if ($pagination) {
                    $pagination->setTotalRows(
                        $model->countByDateBoundsAndImport(
                            $startDate->format('Y-m-d 00:00:00'),
                            $endDate->format('Y-m-d 23:59:59'),
                            $filterDto->contact,
                            $filterDto->id
                        )
                    );
                }

                break;

            default:
                throw new kmwaRuntimeException(_w('Wrong filter type'));
        }

        /** @var cashCategoryDto[] $categoryDtos */
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact($filterDto->contact)
        );

        $dtoAssembler = new cashTransactionDtoAssembler();
        $dtos = [];
        foreach ($dtoAssembler->generateFromIterator(
            $data,
            $accountDtos,
            $categoryDtos,
            $initialBalance
        ) as $id => $dto) {
            $dtos[$id] = $dto;
        }

        return $dtos;
    }

    /**
     * @param DateTime                     $startDate
     * @param DateTime                     $endDate
     * @param waContact                    $contractor
     * @param cashTransactionPageFilterDto $filterDto
     *
     * @return array
     * @throws waException
     */
    public function findForContractor(
        DateTime $startDate,
        DateTime $endDate,
        waContact $contractor,
        cashTransactionPageFilterDto $filterDto
    ): array {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);

        $accountDtos = [];
        foreach (cash()->getEntityRepository(cashAccount::class)->findAllActiveForContact($filterDto->contact) as $a) {
            $accountDtos[$a->getId()] = cashAccountDto::fromEntity($a);
        }

        $data = $model->getContractorTransactionsByDateBounds(
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59'),
            $contractor,
            $filterDto->contact,
            false
        );

        /** @var cashCategoryDto[] $categoryDtos */
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAllActiveForContact($filterDto->contact)
        );

        $dtoAssembler = new cashTransactionDtoAssembler();
        $dtos = [];
        foreach ($dtoAssembler->generateFromIterator($data, $accountDtos, $categoryDtos) as $id => $dto) {
            $dtos[$id] = $dto;
        }

        return $dtos;
    }

    /**
     * @param int    $repeatingId
     * @param string $date
     *
     * @return cashTransaction[]
     * @throws waException
     */
    public function findAllByRepeatingIdAndAfterDate($repeatingId, $date): array
    {
        return $this->findByQuery(
            $this->getModel()->select('*')->where(
                'repeating_id = i:id_old and date >= s:date and is_archived = 0',
                ['id_old' => $repeatingId, 'date' => $date]
            )
        );
    }

    /**
     * @param $repeatingId
     *
     * @return cashTransaction|null
     * @throws waException
     */
    public function findLastByRepeatingId($repeatingId): ?cashTransaction
    {
        $last = $this->findByQuery(
            $this->getModel()
                ->query(
                    'select * from cash_transaction where repeating_id = i:repeating_id and is_archived = 0 order by id desc limit 1',
                    ['repeating_id' => $repeatingId]
                ),
            false
        );

        return $last instanceof cashTransaction ? $last : null;
    }

    public function findFirstForAccount(cashAccount $account): ?cashTransaction
    {
        $initialBalanceSql = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(['ct.*'])
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                    'ct.account_id = i:account_id',
                ]
            )
            ->addParam('account_id', $account->getId())
            ->join(['join cash_account ca on ct.account_id = ca.id'])
            ->orderBy(['ct.date ASC'])
            ->limit(1);

        $data = $initialBalanceSql->query()->fetchAssoc();

        if (!$data) {
            return null;
        }

        return $this->generateWithData($data);
    }

    public function findFirstForCurrency(cashCurrencyVO $currencyVO): ?cashTransaction
    {
        $initialBalanceSql = (new cashSelectQueryParts(cash()->getModel(cashTransaction::class)))
            ->select(['ct.*'])
            ->from('cash_transaction', 'ct')
            ->andWhere(
                [
                    'ct.is_archived = 0',
                    'ca.is_archived = 0',
                    'ca.currency = s:currency',
                ]
            )
            ->addParam('currency', $currencyVO->getCode())
            ->join(['join cash_account ca on ct.account_id = ca.id'])
            ->orderBy(['ct.date ASC'])
            ->limit(1);

        $data = $initialBalanceSql->query()->fetchAssoc();

        if (!$data) {
            return null;
        }

        return $this->generateWithData($data);
    }
}
