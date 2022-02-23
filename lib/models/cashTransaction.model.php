<?php

/**
 * Class cashTransactionModel
 */
class cashTransactionModel extends cashModel
{
    protected $table = 'cash_transaction';

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $account
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndAccount(
        $startDate,
        $endDate,
        waContact $contact,
        $account = null,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $whereAccountSql = '';
        if ($account) {
            $whereAccountSql = ' and ct.account_id = i:account_id';
        }

        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact, $account);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

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
order by ct.date desc, ct.id desc
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'account_id' => $account,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $createContactId
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndCreateContactId(
        $startDate,
        $endDate,
        waContact $contact,
        $createContactId = null,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $whereContactSql = '';
        if ($createContactId) {
            $whereContactSql = ' and ct.create_contact_id = i:create_contact_id';
        }

        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
            $contact,
            $createContactId
        );
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

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
order by ct.date desc, ct.id desc
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'create_contact_id' => $createContactId,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $contractorContactId
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndContractorContactId(
        $startDate,
        $endDate,
        waContact $contact,
        $contractorContactId = null,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $whereContactSql = '';
        if ($contractorContactId) {
            $whereContactSql = ' and ct.contractor_contact_id = i:contractor_contact_id';
        }

        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount(
            $contact,
            $contractorContactId
        );
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

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
order by ct.date desc, ct.id desc
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'contractor_contact_id' => $contractorContactId,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contractor
     * @param waContact $contact
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     * @throws waException
     */
    public function getContractorTransactionsByDateBounds(
        $startDate,
        $endDate,
        waContact $contractor,
        waContact $contact,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $whereAccountSql = '';

        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      and ct.contractor_contact_id = i:contractor_contact_id
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
order by ct.date desc, ct.id desc
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'contractor_contact_id' => $contractor->getId(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $account
     *
     * @return int
     */
    public function countByDateBoundsAndAccount($startDate, $endDate, waContact $contact, $account = null): int
    {
        $whereAccountSql = '';
        if ($account) {
            $whereAccountSql = ' and ct.account_id = i:account_id';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact, $account);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select count(ct.id)
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'account_id' => $account,
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int       $importId
     *
     * @return int
     * @throws waException
     */
    public function countByDateBoundsAndImport($startDate, $endDate, waContact $contact, $importId): int
    {
        $whereImportSql = ' and ct.import_id = i:import_id';

        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');
        $transactionAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);

        $sql = <<<SQL
select count(ct.id)
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereImportSql}
      and {$transactionAccessSql}
      and {$categoryAccessSql}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'import_id' => $importId,
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string         $startDate
     * @param string         $endDate
     * @param waContact      $contact
     * @param cashCurrencyVO $currency
     *
     * @return int
     * @throws waException
     */
    public function countByDateBoundsAndCurrency(
        $startDate,
        $endDate,
        waContact $contact,
        cashCurrencyVO $currency
    ): int {
        $whereImportSql = ' and ca.currency = s:currency_code';

        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');
        $transactionAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);

        $sql = <<<SQL
select count(ct.id)
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereImportSql}
      and {$transactionAccessSql}
      and {$categoryAccessSql}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currency_code' => $currency->getCode(),
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndCategory(
        $startDate,
        $endDate,
        waContact $contact,
        $category = null,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        switch (true) {
            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $whereAccountSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            default:
                $whereAccountSql = ' and ct.category_id = i:category_id';
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
        }

        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

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
order by ct.date desc, ct.id desc
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'category_id' => $category,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     *
     * @return int
     */
    public function countByDateBoundsAndCategory($startDate, $endDate, waContact $contact, $category = null): int
    {
        $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithMinimumAccess(
            $contact,
            'ct',
            'account_id'
        );
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        switch (true) {
            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $whereAccountSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $whereAccountSql2 = sprintf(' and ct2.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            default:
                $whereAccountSql = ' and ct.category_id = i:category_id';
                $whereAccountSql2 = ' and ct2.category_id = i:category_id';
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
        }

        $sql = <<<SQL
select count(ct.id)
from cash_transaction ct
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.is_archived = 0 and ct2.date < s:startDate {$whereAccountSql2})) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
{$joinCategory}
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
      and {$accountAccessSql}
      and {$categoryAccessSql}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'category_id' => $category,
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int       $import
     * @param bool      $returnResult
     * @param int|null  $start
     * @param int|null  $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndImport(
        $startDate,
        $endDate,
        waContact $contact,
        $import,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

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
order by ct.date, ct.id
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'import_id' => $import,
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string         $startDate
     * @param string         $endDate
     * @param waContact      $contact
     * @param cashCurrencyVO $currency
     * @param bool           $returnResult
     * @param int|null       $start
     * @param int|null       $limit
     *
     * @return waDbResultIterator|array
     * @throws waException
     */
    public function getByDateBoundsAndCurrency(
        $startDate,
        $endDate,
        waContact $contact,
        cashCurrencyVO $currency,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        $limits = '';
        if ($start !== null && $limit !== null) {
            $limits = 'limit i:start, i:limit';
        }

        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
    and ct.is_archived = 0
    and ca.currency = s:currency_code
    and {$accountAccessSql}
    and {$categoryAccessSql}
order by ct.date, ct.id
{$limits}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currency_code' => $currency->getCode(),
                'start' => $start,
                'limit' => $limit,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $account
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByDay(
        $startDate,
        $endDate,
        waContact $contact,
        $account = null
    ): array {
        $sql = <<<SQL
select ca.currency,
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash,
       if(ct.amount < 0, 'expense', 'income') cd,
       ct.date,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and %s
    and %s
    and ct.is_archived = 0
group by ct.date, concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndAccount(
            $sql,
            $startDate,
            $endDate,
            $contact,
            $account ? [$account] : []
        );
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int       $importId
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndImportGroupByDay(
        $startDate,
        $endDate,
        waContact $contact,
        $importId
    ): array {
        $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithMinimumAccess($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select ca.currency,
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash,      
       if(ct.amount < 0, 'expense', 'income') cd,
       ct.date,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and {$accountAccessSql}
    and {$categoryAccessSql}
    and ct.import_id = i:import_id
    and ct.is_archived = 0
group by ct.date, concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by ct.date
SQL;

        return $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'import_id' => $importId,
                ]
            )
            ->fetchAll('date', 2);
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByDay(
        $startDate,
        $endDate,
        waContact $contact,
        $category = null
    ): array {
        $sql = <<<SQL
select ca.currency,
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash,
       if(ct.amount < 0, 'expense', 'income') cd,
       ct.date,
       ct.category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and %s
    and %s
    and ct.is_archived = 0
group by ct.date, concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, $contact, $category);
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     *
     * @return array
     */
    public function getDateBoundsByAccounts($startDate, $endDate, $account = null): array
    {
        $accountsSql = $account ? ' and ct.account_id in (i:account_id)' : '';

        $sql = <<<SQL
select min(ct.date) startDate, max(ct.date) endDate
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$accountsSql}
    and ct.is_archived = 0
SQL;
        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'account_id' => $account,
                ]
            )
            ->fetchAssoc();

        return $data;
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return array
     */
    public function getDateBoundsByCategories($startDate, $endDate, $category = null): array
    {
        $categorySql = $category ? ' and ct.category_id = i:category_id' : '';

        $sql = <<<SQL
select min(ct.date) startDate, max(ct.date) endDate
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$categorySql}
    and ct.is_archived = 0
SQL;
        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'category_id' => $category,
                ]
            )
            ->fetchAssoc();

        return $data;
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $accounts
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByMonth(
        $startDate,
        $endDate,
        waContact $contact,
        $accounts = null
    ): array {
        $sql = <<<SQL
select ca.currency,
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash,
       DATE_FORMAT(ct.date, '%%Y-%%m') date,
       if(ct.amount < 0, 'expense', 'income') cd,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and %s
    and %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $contact, (array) $accounts);
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByMonth(
        $startDate,
        $endDate,
        waContact $contact,
        $category = null
    ): array {
        $sql = <<<SQL
select ca.currency,
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash,
       DATE_FORMAT(ct.date, '%%Y-%%m') date,
       if(ct.amount < 0, 'expense', 'income') cd,
       ct.category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and %s
    and %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, $contact, $category);
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $account
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByDay(
        $startDate,
        $endDate,
        waContact $contact,
        $account = null
    ): array {
        $sql = <<<SQL
select ct.date,
       ca.id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    -- and %s
    -- and %s
    and ct.is_archived = 0
group by ct.date, ca.id
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $contact, $account);
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int    $importId
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndImportGroupByDay($startDate, $endDate, $importId): array
    {
        $sql = <<<SQL
select ct.date,
       ca.id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.import_id = i:import_id
    and ct.is_archived = 0
group by ct.date, ca.id
order by ct.date
SQL;

        return $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'import_id' => $importId,
                ]
            )
            ->fetchAll('date', 2);
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $accounts
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByAccount(
        $startDate,
        $endDate,
        waContact $contact,
        $accounts = null
    ): array {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact, $accounts);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';
        $sql = <<<SQL
select /*concat(ca.currency,'_',ifnull(ct.category_id,if(ct.amount < 0, 'expense', 'income'))) hash,*/
       concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$accountsSql}
    and ct.is_archived = 0
    and {$accountAccessSql}
    and {$categoryAccessSql}
group by concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
SQL;

        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'account_ids' => $accounts ? [$accounts] : [],
                ]
            )
            ->fetchAll();

        return array_column($data, 'hash');
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int       $importId
     *
     * @return array
     * @throws waException
     */
    public function getCategoriesAndCurrenciesHashByImport($startDate, $endDate, waContact $contact, $importId): array
    {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.import_id = i:import_id
    and ct.is_archived = 0
    and {$accountAccessSql}
    and {$categoryAccessSql}
group by concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
SQL;

        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'import_id' => $importId,
                ]
            )
            ->fetchAll();

        return array_column($data, 'hash');
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     *
     * @return array
     */
    public function getExistingAccountsBetweenDates($startDate, $endDate, waContact $contact): array
    {
        $accountTransactionAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $accountAccessSql = cash()->getContactRights()->getSqlForAccountJoinWithFullAccess($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $sql = <<<SQL
select ct.account_id
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.is_archived = 0
    and {$accountTransactionAccessSql}
    and {$categoryAccessSql}
    and {$accountAccessSql}
group by ct.account_id
SQL;

        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                ]
            )
            ->fetchAll();

        return array_column($data, 'account_id');
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByCategory(
        $startDate,
        $endDate,
        waContact $contact,
        $category = null
    ): array {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        switch (true) {
            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $categorySql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                break;

            default:
                $categorySql = ' and ct.category_id = i:category_id';
        }

        $sql = <<<SQL
select concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       ) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$categorySql}
    and ct.is_archived = 0
    and {$accountAccessSql}
    and {$categoryAccessSql}
group by concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
SQL;

        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'category_id' => $category,
                ]
            )
            ->fetchAll();

        return array_column($data, 'hash');
    }

    /**
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $accounts
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByMonth(
        $startDate,
        $endDate,
        waContact $contact,
        $accounts = null
    ): array {
        $sql = <<<SQL
select DATE_FORMAT(ct.date, '%%Y-%%m') date,
       ca.id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    -- and %s
    -- and %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), ca.id
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount(
            $sql,
            $startDate,
            $endDate,
            $contact,
            $accounts ? [$accounts] : []
        );
    }

    /**
     * @param int    $repeatingId
     * @param string $date
     *
     * @return int
     */
    public function countRepeatingTransactionsFromDate($repeatingId, $date): int
    {
        return (int) $this->select('count(id)')->where(
            'repeating_id = i:id and date >= s:date and is_archived = 0',
            ['id' => $repeatingId, 'date' => $date]
        )->fetchField();
    }

    /**
     * @param int    $repeatingId
     * @param string $date
     *
     * @return bool|resource
     */
    public function deleteAllByRepeatingIdAfterDate($repeatingId, $date)
    {
        return $this->exec(
            "delete from {$this->table} where repeating_id = i:id_old and date >= s:date",
            ['id_old' => $repeatingId, 'date' => $date]
        );
    }

    /**
     * @param int $categoryId
     *
     * @return bool|waDbResultUpdate|null
     */
    public function archiveByCategoryId($categoryId)
    {
        return $this->updateByField(
            'category_id',
            $categoryId,
            ['is_archived' => 1, 'update_datetime' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * @param int $oldCategoryId
     * @param int $newCategoryId
     *
     * @return bool|waDbResultUpdate|null
     */
    public function changeCategoryId($oldCategoryId, $newCategoryId)
    {
        return $this->updateByField('category_id', $oldCategoryId, ['category_id' => $newCategoryId]);
    }

    /**
     * @param int $accountId
     *
     * @return bool|waDbResultUpdate|null
     */
    public function archiveByAccountId($accountId)
    {
        return $this->updateByField(
            'account_id',
            $accountId,
            ['is_archived' => 1, 'update_datetime' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * @return bool
     */
    public function hasNoCategoryExpenses()
    {
        return (int) $this
            ->select('count(*)')
            ->where('category_id = ? and is_archived = 0', [cashCategoryFactory::NO_CATEGORY_EXPENSE_ID])
            ->fetchField();
    }

    /**
     * @param string $source
     * @param string $hash
     * @param string $date
     *
     * @return bool|resource
     */
    public function deleteBySourceAndHashAfterDate($source, $hash, $date)
    {
        return $this->exec(
            "delete from {$this->table} where external_source = s:source and date > s:date",
            ['source' => $source, 'hash' => $hash, 'date' => $date]
        );
    }

    /**
     * @param string $source
     *
     * @return bool|resource
     */
    public function deleteBySource($source)
    {
        return $this->exec(
            "delete from {$this->table} where external_source = s:source",
            ['source' => $source]
        );
    }

    /**
     * @param string $source
     * @param string $hash
     * @param string $date
     * @param array  $data
     *
     * @return bool|resource
     */
    public function updateAmountBySourceAndHashAfterDate($source, $hash, $date, array $data)
    {
        return $this->exec(
            sprintf(
                "update {$this->table} 
                set %s, update_datetime = s:datetime 
                where external_source = s:source and date >= s:date and external_hash = s:hash",
                implode(
                    ',',
                    array_reduce(
                        $data,
                        static function ($sqlParams, $value) {
                            $sqlParams[] = sprintf('%s = %s:%s', $value[1], $value[0], $value[1]);

                            return $sqlParams;
                        },
                        []
                    )
                )
            ),
            [
                'source' => $source,
                'hash' => $hash,
                'date' => $date,
                'datetime' => date('Y-m-d H:i:s'),
            ] + array_reduce(
                $data,
                static function ($sqlParams, $value) {
                    $sqlParams[$value[1]] = $value[2];

                    return $sqlParams;
                },
                []
            )
        );
    }

    /**
     * @return bool
     */
    public function hasNoCategoryIncomes()
    {
        return (int) $this
            ->select('count(*)')
            ->where('category_id = ? and is_archived = 0', [cashCategoryFactory::NO_CATEGORY_INCOME_ID])
            ->fetchField();
    }

    /**
     * @param string    $sql
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param array     $accounts
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndAccount(
        $sql,
        $startDate,
        $endDate,
        waContact $contact,
        $accounts = []
    ): array {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact, $accounts);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';
        $sql = sprintf($sql, $accountsSql, $accountAccessSql, $categoryAccessSql);

        return $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'account_ids' => $accounts,
                ]
            )
            ->fetchAll('date', 2);
    }

    /**
     * @param string    $sql
     * @param string    $startDate
     * @param string    $endDate
     * @param waContact $contact
     * @param int|null  $category
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndCategory(
        $sql,
        $startDate,
        $endDate,
        waContact $contact,
        $category
    ): array {
        $accountAccessSql = cash()->getContactRights()->getSqlForFilterTransactionsByAccount($contact);
        $categoryAccessSql = cash()->getContactRights()->getSqlForCategoryJoin($contact, 'ct', 'category_id');

        switch (true) {
            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $categoriesSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                break;

            default:
                $categoriesSql = ' and ct.category_id = i:category_id';
        }

        $sql = sprintf($sql, $categoriesSql, $accountAccessSql, $categoryAccessSql);

        return $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'category_id' => $category,
                ]
            )
            ->fetchAll('date', 2);
    }

    /**
     * @param int $repeatingId
     *
     * @return bool|resource
     */
    public function deleteAllRepeating($repeatingId)
    {
        return $this->deleteByField('repeating_id', $repeatingId);
    }

    /**
     * @param int $repeatingId
     * @param int $transactionId
     *
     * @return bool|resource
     */
    public function deleteAllRepeatingAfterTransaction($repeatingId, $transactionId)
    {
        return $this->exec(
            'delete from cash_transaction where repeating_id = i:rid and id > i:tid  and is_archived = 0',
            ['rid' => $repeatingId, 'tid' => $transactionId]
        );
    }

    /**
     * @param int $repeatingId
     * @param int $transactionId
     *
     * @return bool|resource
     */
    public function countAllRepeatingAfterTransaction($repeatingId, $transactionId)
    {
        return (int) $this->query(
            'select count(id) from cash_transaction where repeating_id = i:rid and id > i:tid and is_archived = 0',
            ['rid' => $repeatingId, 'tid' => $transactionId]
        )->fetchField();
    }

    /**
     * @param int $repeatingId
     * @param int $transactionId
     *
     * @return bool|resource
     */
    public function countAllArchivedRepeatingAfterTransaction($repeatingId, $transactionId)
    {
        return (int) $this->query(
            'select count(id) from cash_transaction where repeating_id = i:rid and id > i:tid and is_archived = 1',
            ['rid' => $repeatingId, 'tid' => $transactionId]
        )->fetchField();
    }

    /**
     * @param int $repeatingId
     * @param int $transactionId
     *
     * @return array
     */
    public function getAllRepeatingIdsAfterTransaction($repeatingId, $transactionId): array
    {
        $ids = $this->query(
            'select id from cash_transaction where repeating_id = i:rid and id > i:tid and is_archived = 0',
            ['rid' => $repeatingId, 'tid' => $transactionId]
        )->fetchAll();

        return $ids ? array_column($ids, 'id') : [];
    }

    /**
     * @return array
     */
    public function getYearsWithTransactions(): array
    {
        return array_column(
            $this->query(
                'select year(`date`) transaction_year from cash_transaction where is_archived = 0 group by year(`date`) order by year(`date`)'
            )->fetchAll(),
            'transaction_year'
        );
    }

    /**
     * @param waContact $contact
     *
     * @return int
     */
    public function countTransfers(waContact $contact)
    {
        try {
            return $this->countByField(
                [
                    'category_id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
                    'create_contact_id' => $contact->getId(),
                ]
            );
        } catch (Exception $exception) {
            return 0;
        }
    }

    /**
     * @param array $ids
     *
     * @return waDbResultIterator
     */
    public function getAllIteratorByIds(array $ids)
    {
        return $this->query(sprintf('select * from %s where id in (i:ids)', $this->table), ['ids' => $ids])
            ->getIterator();
    }
}
