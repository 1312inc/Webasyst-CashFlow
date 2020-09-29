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
     * cashTransactionFilterService constructor.
     *
     * @throws waException
     */
    public function __construct()
    {
        $this->model = cash()->getModel(cashTransaction::class);
    }

    /**
     * @param cashTransactionFilterParamsDto $dto
     *
     * @return array|waDbResultIterator
     * @throws kmwaForbiddenException
     * @throws waException
     */
    public function getResults(cashTransactionFilterParamsDto $dto)
    {
        $rights = cash()->getContactRights();

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
            $dto->contact,
            $dto->accountId
        );
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($dto->contact, 'ct', 'category_id');

        $limits = '';
        if ($dto->start !== null && $dto->limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        if ($dto->reverse) {
            $order = 'order by ct.date desc, ct.id desc';
        } else {
            $order = 'order by ct.date, ct.id';
        }

        $sqlParams = [
            'startDate' => $dto->startDate->format('Y-m-d H:i:s'),
            'endDate' => $dto->endDate->format('Y-m-d H:i:s'),
            'start' => $dto->start,
            'limit' => $dto->limit,
        ];

        switch (true) {
            case $dto->accountId:
                if (!$rights->hasMinimumAccessToAccount($dto->contact, $dto->accountId)) {
                    throw new kmwaForbiddenException(_w('You have no access to this account'));
                }

                $whereAccountSql = ' and ct.account_id = i:account_id';
                $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
{$order}
{$limits}
SQL;
                $sqlParams['account_id'] = $dto->accountId;

                break;

            case $dto->categoryId:
                if (!$rights->hasMinimumAccessToCategory($dto->contact, $dto->categoryId)) {
                    throw new kmwaForbiddenException(_w('You have no access to this category'));
                }

                $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithMinimumAccess(
                    $dto->contact,
                    'ct',
                    'account_id'
                );

                $whereAccountSql = ' and ct.category_id = i:category_id';
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
{$joinCategory}
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
{$order}
{$limits}
SQL;
                $sqlParams['category_id'] = $dto->categoryId;

                break;

            case $dto->createContactId:
                if (!$rights->isAdmin($dto->contact)) {
                    throw new kmwaForbiddenException(_w('You have no access to this contact'));
                }

                $whereContactSql = ' and ct.create_contact_id = i:create_contact_id';
                $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereContactSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
{$order}
{$limits}
SQL;
                $sqlParams['create_contact_id'] = $dto->createContactId;

                break;

            case $dto->contractorContactId:
                if (!$rights->isAdmin($dto->contact)) {
                    throw new kmwaForbiddenException(_w('You have no access to this contractor'));
                }

                $whereContactSql = ' and ct.contractor_contact_id = i:contractor_contact_id';
                $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereContactSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
{$order}
{$limits}
SQL;
                $sqlParams['contractor_contact_id'] = $dto->contractorContactId;


                break;

            case $dto->importId:
                if (!$rights->isAdmin($dto->contact)) {
                    throw new kmwaForbiddenException(_w('You have no access to this contractor'));
                }

                $sql = <<<SQL
select ct.*,
       (@balance := @balance + ct.amount) as balance
from cash_transaction ct
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.is_archived = 0 and ct2.date < s:startDate)) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
    and ct.import_id = i:import_id
    and ct.is_archived = 0
    and {$accountAccessSql}
    and {$categoryAccessSql}
{$order}
{$limits}
SQL;
                $sqlParams['import_id'] = $dto->importId;

                break;

            default:
                $whereAccountSql = '';
                $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
{$order}
{$limits}
SQL;
        }

        $query = $this->model->query($sql, $sqlParams);

        return !$dto->returnIterator ? $query->fetchAll() : $query->getIterator();
    }
}
