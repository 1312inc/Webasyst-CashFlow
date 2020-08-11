<?php

/**
 * Class cashTransactionModel
 */
class cashTransactionModel extends cashModel
{
    protected $table = 'cash_transaction';

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     * @param bool     $returnResult
     * @param int|null $start
     * @param int|null $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndAccount(
        $startDate,
        $endDate,
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

        $sql = <<<SQL
select ct.*
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     *
     * @return int
     */
    public function countByDateBoundsAndAccount($startDate, $endDate, $account = null)
    {
        $whereAccountSql = '';
        if ($account) {
            $whereAccountSql = ' and ct.account_id = i:account_id';
        }

        $sql = <<<SQL
select count(ct.id)
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'account_id' => $account
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     * @param bool     $returnResult
     * @param int|null      $start
     * @param int|null      $limit
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndCategory(
        $startDate,
        $endDate,
        $category = null,
        $returnResult = false,
        $start = null,
        $limit = null
    ) {
        switch (true) {
            case $category > 0:
                $whereAccountSql = ' and ct.category_id = i:category_id';
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_EXPENSE_ID:
                $whereAccountSql = ' and ct.category_id is null and ct.amount < 0';
                $joinCategory = '';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_INCOME_ID:
                $whereAccountSql = ' and ct.category_id is null and ct.amount > 0';
                $joinCategory = '';
                break;

            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $whereAccountSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            default:
                $whereAccountSql = '';
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return int
     */
    public function countByDateBoundsAndCategory($startDate, $endDate, $category = null)
    {
        switch (true) {
            case $category > 0:
                $whereAccountSql = ' and ct.category_id = i:category_id';
                $whereAccountSql2 = ' and ct2.category_id = i:category_id';
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_EXPENSE_ID:
                $whereAccountSql = ' and ct.category_id is null and ct.amount < 0';
                $whereAccountSql2 = ' and ct2.category_id is null and ct2.amount < 0';
                $joinCategory = '';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_INCOME_ID:
                $whereAccountSql = ' and ct.category_id is null and ct.amount > 0';
                $whereAccountSql2 = ' and ct2.category_id is null and ct2.amount > 0';
                $joinCategory = '';
                break;

            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $whereAccountSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $whereAccountSql2 = sprintf(' and ct2.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                $joinCategory = ' join cash_category cc on ct.category_id = cc.id';
                break;

            default:
                $whereAccountSql = '';
                $whereAccountSql2 = '';
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
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'category_id' => $category
            ]
        );

        return (int) $query->fetchField();
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int    $import
     * @param bool   $returnResult
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndImport($startDate, $endDate, $import, $returnResult = false)
    {
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
order by ct.date, ct.id
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'import_id' => $import,
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByDay($startDate, $endDate, $account = null)
    {
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
            $account ? [$account] : []
        );
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int    $importId
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndImportGroupByDay($startDate, $endDate, $importId)
    {
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByDay($startDate, $endDate, $category = null)
    {
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
    and ct.is_archived = 0
group by ct.date, concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, $category);
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     *
     * @return array
     */
    public function getDateBoundsByAccounts($startDate, $endDate, $account = null)
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
    public function getDateBoundsByCategories($startDate, $endDate, $category = null)
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByMonth($startDate, $endDate, $accounts = null)
    {
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
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, (array)$accounts);
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByMonth($startDate, $endDate, $category = null)
    {
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
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), concat(
           ca.currency,
           ifnull(ct.category_id, '_'),
           if(ct.amount < 0, 'expense', 'income')
       )
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, $category);
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $account
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByDay($startDate, $endDate, $account = null)
    {
        $sql = <<<SQL
select ct.date,
       ca.id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and ct.is_archived = 0
group by ct.date, ca.id
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $account);
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int    $importId
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndImportGroupByDay($startDate, $endDate, $importId)
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByAccount($startDate, $endDate, $accounts = null)
    {
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
     * @param string $startDate
     * @param string $endDate
     * @param int    $importId
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByImport($startDate, $endDate, $importId)
    {
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getExistingAccountsBetweenDates($startDate, $endDate)
    {
        $sql = <<<SQL
select ct.account_id
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.is_archived = 0
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByCategory($startDate, $endDate, $category = null)
    {
        switch (true) {
            case $category > 0:
                $categorySql = ' and ct.category_id = i:category_id';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_EXPENSE_ID:
                $categorySql = ' and ct.category_id is null and ct.amount < 0';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_INCOME_ID:
                $categorySql = ' and ct.category_id is null and ct.amount > 0';
                break;

            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $categorySql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                break;

            default:
                $categorySql = '';
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
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByMonth($startDate, $endDate, $accounts = null)
    {
        $sql = <<<SQL
select DATE_FORMAT(ct.date, '%%Y-%%m') date,
       ca.id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), ca.id
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount(
            $sql,
            $startDate,
            $endDate,
            $accounts ? [$accounts] : []
        );
    }

    /**
     * @param int    $repeatingId
     * @param string $date
     *
     * @return int
     */
    public function countRepeatingTransactionsFromDate($repeatingId, $date)
    {
        return (int)$this->select('count(id)')->where(
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
        return (int)$this
            ->select('count(*)')
            ->where('category_id is null and amount < 0 and is_archived = 0')
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
        return (int)$this
            ->select('count(*)')
            ->where('category_id is null and amount > 0 and is_archived = 0')
            ->fetchField();
    }

    /**
     * @param string $sql
     * @param string $startDate
     * @param string $endDate
     * @param array  $accounts
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts = [])
    {
        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';
        $sql = sprintf($sql, $accountsSql, $accountsSql);

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
     * @param string   $sql
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, $category)
    {
        switch (true) {
            case $category > 0:
                $categoriesSql = ' and ct.category_id = i:category_id';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_EXPENSE_ID:
                $categoriesSql = ' and ct.category_id is null and ct.amount < 0';
                break;

            case $category == cashCategoryFactory::NO_CATEGORY_INCOME_ID:
                $categoriesSql = ' and ct.category_id is null and ct.amount > 0';
                break;

            case $category == cashCategoryFactory::TRANSFER_CATEGORY_ID:
                $categoriesSql = sprintf(' and ct.category_id = %d', cashCategoryFactory::TRANSFER_CATEGORY_ID);
                break;

            default:
                $categoriesSql = '';
        }

        $sql = sprintf($sql, $categoriesSql);

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
            'delete from cash_transaction where repeating_id = i:rid and id > i:tid',
            ['rid' => $repeatingId, 'tid' => $transactionId]
        );
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
}
