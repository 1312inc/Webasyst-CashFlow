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
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.date < s:startDate {$whereAccountSql2})) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      {$whereAccountSql}
order by `datetime`
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
join (select @balance := (select ifnull(sum(ct2.amount),0) from cash_transaction ct2 where ct2.date < s:startDate {$whereAccountSql2})) b
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      {$whereAccountSql}
order by `datetime`
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
       ct.date,
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
group by ct.date, ca.currency, ct.category_id
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
       ct.date,
       0 category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
group by ct.date, ca.currency, ct.category_id
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
where ct.date between s:startDate and s:endDate {$accountsSql}
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
where ct.date between s:startDate and s:endDate {$categorySql}
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
       ct.category_id category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
group by YEAR(ct.date), MONTH(ct.date), ca.currency, ct.category_id
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
       0 category_id,
       ifnull(sum(ct.amount), 0) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id and ca.is_archived = 0
where ct.date between s:startDate and s:endDate %s
group by YEAR(ct.date), MONTH(ct.date), ca.currency, ct.category_id
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
where ct.date between s:startDate and s:endDate %s
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
where ct.date between s:startDate and s:endDate {$accountsSql}
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
where ct.date between s:startDate and s:endDate {$categoriesSql}
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
where ct.date between s:startDate and s:endDate %s
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
