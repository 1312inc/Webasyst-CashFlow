<?php

/**
 * Class cashTransactionModel
 */
class cashTransactionModel extends cashModel
{
    protected $table = 'cash_transaction';

    /**
     * @param string $startDate
     * @param string $endDate
     * @param array  $accounts
     * @param bool   $returnResult
     *
     * @return waDbResultIterator|array
     */
    public function getByDateBoundsAndAccount($startDate, $endDate, array $accounts = [], $returnResult = false)
    {
        $whereAccountSql = '';
        if ($accounts) {
            $whereAccountSql = ' and ct.account_id in (i:account_ids)';
        }

        $sql = <<<SQL
select ct.*,
       (@balance := @balance + ct.amount) as balance
from cash_transaction ct
join (select @balance := (select sum(ct.amount) from cash_transaction ct where ct.date < s:startDate {$whereAccountSql})) b
join cash_account ca on ct.account_id = ca.id
left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      {$whereAccountSql}
order by `datetime`
SQL;
        $query = $this->query(
            $sql,
            ['startDate' => $startDate, 'endDate' => $endDate, 'account_ids' => $accounts]
        );

        return $returnResult ? $query->fetchAll() : $query->getIterator();
    }

    /**
     * @param       $startDate
     * @param       $endDate
     * @param array $accounts
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByDay($startDate, $endDate, array $accounts = [])
    {
        $sql = <<<SQL
select ca.currency,
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       ct.date,
       ct.category_id,
       sum(ct.amount) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id
where ct.date between s:startDate and s:endDate %s
group by ct.date, ca.currency, ct.category_id
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts);
    }

    /**
     * @param       $startDate
     * @param       $endDate
     * @param array $accounts
     *
     * @return array
     */
    public function getSummaryByDateBoundsAndAccountGroupByMonth($startDate, $endDate, array $accounts = [])
    {
        $sql = <<<SQL
select ca.currency,
       concat(ca.currency,'_',ifnull(ct.category_id,0)) hash,
       concat(YEAR(ct.date), '-', MONTH(ct.date)) date,
       ct.category_id,
       sum(ct.amount) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id
where ct.date between s:startDate and s:endDate %s
group by YEAR(ct.date), MONTH(ct.date), ca.currency, ct.category_id
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts);
    }

    /**
     * @param       $startDate
     * @param       $endDate
     * @param array $accounts
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByDay($startDate, $endDate, array $accounts = [])
    {
        $sql = <<<SQL
select ct.date,
       ca.id category_id,
       sum(ct.amount) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id
where ct.date between s:startDate and s:endDate %s
group by ct.date, ca.id
order by ct.date
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts);
    }

    /**
     * @param       $startDate
     * @param       $endDate
     * @param array $accounts
     *
     * @return array
     */
    public function getCategoriesAndCurrenciesHash($startDate, $endDate, array $accounts = [])
    {
        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';
        $sql = <<<SQL
select concat(ca.currency,'_',ifnull(ct.category_id,0)) hash
from cash_transaction ct
     join cash_account ca on ct.account_id = ca.id
where ct.date between s:startDate and s:endDate {$accountsSql}
group by concat(ca.currency,'_',ifnull(ct.category_id,0))
SQL;

        $data = $this
            ->query(
                $sql,
                ['startDate' => $startDate, 'endDate' => $endDate, 'account_ids' => $accounts]
            )
            ->fetchAll();

        return array_column($data, 'hash');
    }

    /**
     * @param       $startDate
     * @param       $endDate
     * @param array $accounts
     *
     * @return array
     */
    public function getBalanceByDateBoundsAndAccountGroupByMonth($startDate, $endDate, array $accounts = [])
    {
        $sql = <<<SQL
select concat(YEAR(ct.date), '-', MONTH(ct.date)) date,
       ca.id category_id,
       sum(ct.amount) summary
from cash_transaction ct
join cash_account ca on ct.account_id = ca.id
where ct.date between s:startDate and s:endDate %s
group by YEAR(ct.date), MONTH(ct.date), ca.id
order by YEAR(ct.date), MONTH(ct.date)
SQL;

        return $this->querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts);
    }

    /**
     * @param $sql
     * @param $startDate
     * @param $endDate
     * @param $accounts
     *
     * @return array
     */
    private function querySummaryByDateBoundsAndAccount($sql, $startDate, $endDate, $accounts)
    {
        $accountsSql = $accounts ? ' and ct.account_id in (i:account_ids)' : '';
        $sql = sprintf($sql, $accountsSql, $accountsSql);

        return $this
            ->query(
                $sql,
                ['startDate' => $startDate, 'endDate' => $endDate, 'account_ids' => $accounts]
            )
            ->fetchAll('date', 2);
    }
}
