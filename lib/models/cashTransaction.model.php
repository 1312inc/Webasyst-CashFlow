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
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndAccount($startDate, $endDate, $account = null, $returnResult = false)
    {
        $whereAccountSql = '';
        if ($account) {
            $whereAccountSql = ' and ct.account_id in (i:account_ids)';
        }
        $whereAccountSql2 = '';
        if ($account) {
            $whereAccountSql2 = ' and ct2.account_id in (i:account_ids)';
        }

        $sql = <<<SQL
select ct.*,
       (@balance := @balance + ct.amount) as balance
from cash_transaction ct
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.is_archived = 0 and ct2.date < s:startDate {$whereAccountSql2})) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
order by ct.date, ct.id
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'account_ids' => $account ? [$account] : [],
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $category
     * @param bool     $returnResult
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndCategory($startDate, $endDate, $category = null, $returnResult = false)
    {
        $whereAccountSql = '';
        if ($category) {
            $whereAccountSql = ' and ct.category_id in (i:category_ids)';
        }
        $whereAccountSql2 = '';
        if ($category) {
            $whereAccountSql2 = ' and ct2.category_id in (i:category_ids)';
        }

        $sql = <<<SQL
select ct.*,
       (@balance := @balance + ct.amount) as balance
from cash_transaction ct
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.is_archived = 0 and ct2.date < s:startDate {$whereAccountSql2})) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ct.is_archived = 0
      {$whereAccountSql}
order by ct.date, ct.id
SQL;

        $query = $this->query(
            $sql,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'category_ids' => $category ? [$category] : [],
            ]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
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
join cash_category cc on ct.category_id = cc.id
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
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       if(ct.amount < 0, 'credit', 'debit') cd,
       ct.date,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
    and ct.is_archived = 0
group by ct.date, ca.currency, ct.category_id, if(ct.amount < 0, 'credit', 'debit')
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
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       if(ct.amount < 0, 'credit', 'debit') cd,
       ct.date,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.import_id = i:import_id
    and ct.is_archived = 0
group by ct.date, ca.currency, ct.category_id, if(ct.amount < 0, 'credit', 'debit')
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
     * @param int|null $categories
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByDay($startDate, $endDate, $categories = null)
    {
        $sql = <<<SQL
select ca.currency,
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       if(ct.amount < 0, 'credit', 'debit') cd,
       ct.date,
       ct.category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
    and ct.is_archived = 0
group by ct.date, ca.currency, ct.category_id, if(ct.amount < 0, 'credit', 'debit')
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndCategory(
            $sql,
            $startDate,
            $endDate,
            $categories ? [$categories] : []
        );
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getDateBoundsByAccounts($startDate, $endDate, $accounts = null)
    {
        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';

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
                    'account_ids' => $accounts ? [$accounts] : [],
                ]
            )
            ->fetchAssoc();

        return $data;
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $categories
     *
     * @return array
     */
    public function getDateBoundsByCategories($startDate, $endDate, $categories = null)
    {
        $categorySql = $categories ? ' and ct.category_id in (i:category_ids)' : '';

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
                    'category_ids' => $categories ? [$categories] : [],
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
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       concat(YEAR(ct.date), '-', MONTH(ct.date)) date,
       if(ct.amount < 0, 'credit', 'debit') cd,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), ca.currency, ct.category_id, if(ct.amount < 0, 'credit', 'debit')
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, (array)$accounts);
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $categories
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndCategoryGroupByMonth($startDate, $endDate, $categories = null)
    {
        $sql = <<<SQL
select ca.currency,
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       concat(YEAR(ct.date), '-', MONTH(ct.date)) date,
       if(ct.amount < 0, 'credit', 'debit') cd,
       ct.category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    %s
    and ct.is_archived = 0
group by YEAR(ct.date), MONTH(ct.date), ca.currency, ct.category_id, if(ct.amount < 0, 'credit', 'debit')
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndCategory(
            $sql,
            $startDate,
            $endDate,
            $categories ? [$categories] : []
        );
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByDay($startDate, $endDate, $accounts = null)
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

        return $this->querySummaryByDateBoundsAndAccount(
            $sql,
            $startDate,
            $endDate,
            $accounts ? [$accounts] : []
        );
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
select concat(ca.currency,'_',ifnull(ct.category_id,0)) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$accountsSql}
    and ct.is_archived = 0
group by concat(ca.currency,'_',ifnull(ct.category_id,0))
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
select concat(ca.currency,'_',ifnull(ct.category_id,0)) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate
    and ct.import_id = i:import_id
    and ct.is_archived = 0
group by concat(ca.currency,'_',ifnull(ct.category_id,0))
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
     * @param int|null $categories
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHashByCategory($startDate, $endDate, $categories = null)
    {
        $categoriesSql = $categories ? ' and ct.category_id in (i:category_ids)' : '';
        $sql = <<<SQL
select concat(ca.currency,'_',ifnull(ct.category_id,0)) hash
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate 
    {$categoriesSql}
    and ct.is_archived = 0
group by concat(ca.currency,'_',ifnull(ct.category_id,0))
SQL;

        $data = $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'category_ids' => $categories ? [$categories] : [],
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
select concat(YEAR(ct.date), '-', MONTH(ct.date)) date,
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
     * @param string $sql
     * @param string $startDate
     * @param string $endDate
     * @param array  $accounts
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, array $accounts)
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
     * @param string $sql
     * @param string $startDate
     * @param string $endDate
     * @param array  $categories
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndCategory($sql, $startDate, $endDate, array $categories)
    {
        $categoriesSql = $categories ? ' and ct.category_id in (i:category_ids)' : '';
        $sql = sprintf($sql, $categoriesSql, $categoriesSql);

        return $this
            ->query(
                $sql,
                [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'category_ids' => $categories,
                ]
            )
            ->fetchAll('date', 2);
    }
}
