<?php

/**
 * Class cashAccountModel
 */
class cashAccountModel extends cashModel
{
    protected $table = 'cash_account';

    /**
     * @return array
     */
    public function getAllActive()
    {
        return $this
            ->select('*')
            ->where('is_archived = 0')
            ->order('sort ASC, id DESC')
            ->fetchAll();
    }

    /**
     * @param string   $startDate
     * @param string   $endDate
     * @param int|null $accounts
     *
     * @return array
     */
    public function getStatDataForAccounts($startDate, $endDate, $accounts = null)
    {
        $accountFilterSql = '';
        if ($accounts) {
            $accountFilterSql = ' and ca.id in (i:accounts)';
        }

        $sql = <<<SQL
select ca.id,
       ca.currency,
       ifnull(t.income, 0) income,
       ifnull(t.expense, 0) expense,
       ifnull(t.summary, 0) summary
from cash_account ca
         left join (
    select ca.id                                id,
           sum(if(ct.amount > 0, ct.amount, 0)) income,
           sum(if(ct.amount < 0, ct.amount, 0)) expense,
           sum(ct.amount)                       summary
    from cash_account ca
             left join cash_transaction ct on ct.account_id = ca.id
             left join cash_category cc on ct.category_id = cc.id
    where ct.date between s:startDate and s:endDate
          and ca.is_archived = 0
          {$accountFilterSql}
    group by ca.id
) t on ca.id = t.id
SQL;

        return $this
            ->query($sql, ['startDate' => $startDate, 'endDate' => $endDate, 'accounts' => (array)$accounts])
            ->fetchAll('id');
    }

    /**
     * @param string     $startDate
     * @param string     $endDate
     * @param string     $filterType
     * @param null|array $filterIds
     *
     * @return array
     */
    public function getStatDetailedCategoryData($startDate, $endDate, $filterType = 'account', $filterIds = null)
    {
        $filterSql = '';
        if ($filterIds) {
            $filterSql = ($filterType === 'account') ? ' and ca.id in (i:filter_ids)' : ' and cc.id in (i:filter_ids)';
        }

        $sql = <<<SQL
select cc.name,
       cc.color,
       ct.category_id id,
       ca.currency,
       sum(if(ct.amount > 0, ct.amount, 0)) income,
       sum(if(ct.amount < 0, ct.amount, 0)) expense,
       sum(ct.amount)                       summary
from cash_transaction ct
         join cash_account ca on ct.account_id = ca.id
         left join cash_category cc on ct.category_id = cc.id
where ct.date between s:startDate and s:endDate
      and ca.is_archived = 0
      {$filterSql}
group by ct.category_id, ca.currency
SQL;

        return $this
            ->query($sql, ['startDate' => $startDate, 'endDate' => $endDate, 'filter_ids' => (array)$filterIds])
            ->fetchAll();
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param array  $categories
     *
     * @return array
     */
    public function getStatDataForCategories($startDate, $endDate, $categories)
    {
        $sql = <<<SQL
select ca.id,
       ca.currency,
       ifnull(t.income, 0) income,
       ifnull(t.expense, 0) expense,
       ifnull(t.summary, 0) summary
from cash_account ca
         left join (
    select ca.id                                id,
           sum(if(ct.amount > 0, ct.amount, 0)) income,
           sum(if(ct.amount < 0, ct.amount, 0)) expense,
           sum(ct.amount)                       summary
    from cash_account ca
             left join cash_transaction ct on ct.account_id = ca.id
             join cash_category cc on ct.category_id = cc.id
    where ct.date between s:startDate and s:endDate
          and ca.is_archived = 0
          and cc.id in (i:categories)
    group by ca.id
) t on ca.id = t.id
SQL;

        return $this
            ->query($sql, ['startDate' => $startDate, 'endDate' => $endDate, 'categories' => $categories])
            ->fetchAll('id');
    }
}
