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
    public function findByDates(
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
                    $initialBalance = (float)ifset($initialBalance, $filterDto->id, 'summary', 0.0);
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
                    $filterDto->id
                );

                break;

            default:
                throw new kmwaRuntimeException(_w('Wrong filter type'));
        }

        /** @var cashCategoryDto[] $categoryDtos */
        $categoryDtos = cashDtoFromEntityFactory::fromEntities(
            cashCategoryDto::class,
            cash()->getEntityRepository(cashCategory::class)->findAll()
        );

        $dtoAssembler = new cashTransactionDtoAssembler();
        $dtos = [];
        foreach ($dtoAssembler->generateFromIterator($data, $accountDtos, $categoryDtos, $initialBalance) as $id => $dto) {
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
     * @return cashTransaction
     * @throws waException
     */
    public function findLastByRepeatingId($repeatingId): cashTransaction
    {
        return $this->findByQuery(
            $this->getModel()
                ->query(
                    'select * from cash_transaction where repeating_id = i:repeating_id and is_archived = 0 order by id desc limit 1',
                    ['repeating_id' => $repeatingId]
                ),
            false
        );
    }
}
