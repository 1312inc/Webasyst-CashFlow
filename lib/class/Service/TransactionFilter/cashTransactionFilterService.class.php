<?php

/**
 * Class cashTransactionFilterService
 */
final class cashTransactionFilterService
{
    /**
     * @var cashTransactionModel
     */
    private $model;

    /**
     * @var cashContactRightsManager
     */
    private $right;

    /**
     * cashTransactionFilterService constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->model = cash()->getModel(cashTransaction::class);
        $this->right = cash()->getContactRights();
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return array|waDbResultIterator|int
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function getResults(cashTransactionFilterParamsDto $dto)
    {
        $sqlParts = $this->getResultsSqlParts($dto);

        $query = $sqlParts->query();

        return !$dto->returnIterator ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return array|waDbResultIterator|int
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function getResultsCount(cashTransactionFilterParamsDto $dto)
    {
        $sqlParts = $this->getResultsSqlParts($dto);

        $sqlParts->select(['count(ct.id) cnt'])
            ->limit(null)
            ->offset(null);

        return (int) $sqlParts->query()->fetchField();
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return array|waDbResultIterator|int
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function getShrinkResultsCount(cashTransactionFilterParamsDto $dto)
    {
        $sqlParts = $this->getResultsSqlParts($dto);

        $sqlRepeatingParts = clone $sqlParts;

        $sqlParts->addAndWhere('ct.repeating_id is null');
        $sqlRepeatingParts->groupBy(['ct.repeating_id']);

        $unionSql = cashSelectQueryParts::union($sqlParts, $sqlRepeatingParts);

        $unionSql->select(['count(union_table.id) cnt'])
            ->limit(null)
            ->offset(null);

        return (int) $unionSql->query()->fetchField();
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return array|waDbResultIterator|int
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function getShrinkResults(cashTransactionFilterParamsDto $dto)
    {
        $sqlParts = $this->getResultsSqlParts($dto);

        $sqlRepeatingParts = clone $sqlParts;

        $sqlParts->addAndWhere('ct.repeating_id is null');
        $sqlRepeatingParts->groupBy(['ct.repeating_id']);

        $unionSql = cashSelectQueryParts::union($sqlParts, $sqlRepeatingParts);

        $query = $unionSql->query();

        return !$dto->returnIterator ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     * @param cashSelectQueryParts           $selectQueryParts
     *
     * @throws kmwaForbiddenException
     */
    private function makeBaseSqlForAccountFilter(
        cashTransactionFilterParamsDto $dto,
        cashSelectQueryParts $selectQueryParts
    ): void {
        if (!$this->right->hasMinimumAccessToAccount($dto->contact, $dto->filter->getAccountId())) {
            throw new kmwaForbiddenException(_w('You have no access to this account'));
        }

        $selectQueryParts->addAndWhere('ct.account_id = i:account_id')
            ->addParam('account_id', $dto->filter->getAccountId());
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     * @param cashSelectQueryParts           $selectQueryParts
     *
     * @throws kmwaForbiddenException
     * @throws waException
     */
    private function makeBaseSqlForCategoryFilter(
        cashTransactionFilterParamsDto $dto,
        cashSelectQueryParts $selectQueryParts
    ): void {
        if (!$this->right->hasMinimumAccessToCategory($dto->contact, $dto->filter->getCategoryId())) {
            throw new kmwaForbiddenException(_w('You have no access to this category'));
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithMinimumAccess(
            $dto->contact,
            'ct',
            'account_id'
        );

        $sqlParams['category_id'] = $dto->filter->getCategoryId();

        $selectQueryParts->addAndWhere('ct.category_id = i:category_id')
            ->addAndWhere($accountAccessSql, 'accountAccessSql')
            ->addParam('category_id', $dto->filter->getCategoryId());
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     * @param cashSelectQueryParts           $selectQueryParts
     *
     * @throws waException
     */
    private function makeBaseSqlForCurrencyFilter(
        cashTransactionFilterParamsDto $dto,
        cashSelectQueryParts $selectQueryParts
    ): void {
        $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithMinimumAccess(
            $dto->contact,
            'ct',
            'account_id'
        );

        $selectQueryParts->addAndWhere('ca.currency = s:currency')
            ->addAndWhere($accountAccessSql, 'accountAccessSql')
            ->addParam('currency', $dto->filter->getCurrency());
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     * @param cashSelectQueryParts           $selectQueryParts
     *
     * @throws kmwaForbiddenException
     */
    private function makeBaseSqlForContractorFilter(
        cashTransactionFilterParamsDto $dto,
        cashSelectQueryParts $selectQueryParts
    ): void {
        if (!$this->right->isAdmin($dto->contact)) {
            throw new kmwaForbiddenException(_w('You have no access to this contractor'));
        }

        $selectQueryParts->addAndWhere('ct.contractor_contact_id = i:contractor_contact_id')
            ->addParam('contractor_contact_id', $dto->filter->getContractorId());
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return cashSelectQueryParts
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function getResultsSqlParts(cashTransactionFilterParamsDto $dto): cashSelectQueryParts
    {
        $sqlParts = new cashSelectQueryParts(cash()->getModel());

        $sqlParts->select(['ct.*'])
            ->from('cash_transaction', 'ct')
            ->join(
                [
                    'join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0',
                    'left join cash_category cc on ct.category_id = cc.id',
                ]
            )
            ->andWhere(
                [
                    'ct.date between s:startDate and s:endDate',
                    'ct.is_archived = 0',
                    'accountAccessSql' => cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
                        $dto->contact,
                        $dto->filter->getCategoryId()
                    ),
                    'categoryAccessSql' => cash()->getContactRights()->getSqlForCategoryJoin(
                        $dto->contact,
                        'ct',
                        'category_id'
                    ),
                ]
            );

        if ($dto->start !== null && $dto->limit !== null) {
            $sqlParts->limit($dto->limit)
                ->offset($dto->start);
        }

        if ($dto->reverse) {
            $sqlParts->orderBy(['ct.date desc', 'ct.id desc']);
        } else {
            $sqlParts->orderBy(['ct.date', 'ct.id']);
        }

        $sqlParts->params(
            [
                'startDate' => $dto->startDate->format('Y-m-d H:i:s'),
                'endDate' => $dto->endDate->format('Y-m-d H:i:s'),
                'start' => $dto->start,
                'limit' => $dto->limit,
            ]
        );

        switch (true) {
            case null !== $dto->filter->getAccountId():
                $this->makeBaseSqlForAccountFilter($dto, $sqlParts);

                break;

            case null !== $dto->filter->getCategoryId():
                $this->makeBaseSqlForCategoryFilter($dto, $sqlParts);

                break;

            case null !== $dto->filter->getCurrency():
                $this->makeBaseSqlForCurrencyFilter($dto, $sqlParts);

                break;

            case null !== $dto->filter->getContractorId():
                $this->makeBaseSqlForContractorFilter($dto, $sqlParts);

                break;
        }

        return $sqlParts;
    }
}
