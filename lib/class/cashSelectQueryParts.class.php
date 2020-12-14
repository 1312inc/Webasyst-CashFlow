<?php

/**
 * Class cashSelectQueryParts
 */
final class cashSelectQueryParts
{
    /**
     * @var array
     */
    private $andWhere = [];

    /**
     * @var array
     */
    private $orderBy = [];

    /**
     * @var array
     */
    private $groupBy = [];

    /**
     * @var array
     */
    private $select = [];

    /**
     * @var array
     */
    private $join = [];

    /**
     * @var string
     */
    private $from = '';

    /**
     * @var null|int
     */
    private $limit = null;

    /**
     * @var null|int
     */
    private $offset = null;

    /**
     * @var string
     */
    private $baseSql = '';

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var cashModel
     */
    private $model;

    /**
     * @var string
     */
    private $alias;

    /**
     * cashSelectQueryParts constructor.
     *
     * @param cashModel $model
     */
    public function __construct(cashModel $model)
    {
        $this->model = $model;
        $this->baseSql = <<<SQL
__SELECT_PART__
__FROM_PART__
__JOIN_PART__
__WHERE_PART__
__GROUP_BY_PART__
__ORDER_BY_PART__
SQL;
    }

    /**
     * @param $baseSql
     *
     * @return waDbResultSelect
     */
    public function query($baseSql = '')
    {
        return $this->model->query($this->getSql($baseSql), $this->params);
    }

    /**
     * @param string $baseSql
     *
     * @return string
     */
    public function getSql($baseSql = '')
    {
        if (!$baseSql) {
            $baseSql = $this->baseSql;
        }

        $sql = str_replace(
            [
                '__SELECT_PART__',
                '__FROM_PART__',
                '__JOIN_PART__',
                '__WHERE_PART__',
                '__GROUP_BY_PART__',
                '__ORDER_BY_PART__',
            ],
            [
                sprintf('select %s', implode(', ', $this->select)),
                sprintf('from %s %s', $this->from, $this->alias),
                $this->join ? implode(' ', $this->join) : '',
                $this->andWhere ? sprintf('where (%s)', implode(') and (', $this->andWhere)) : '',
                $this->groupBy ? sprintf('group by (%s)', implode('), (', $this->groupBy)) : '',
                $this->orderBy ? sprintf('order by %s', implode(', ', $this->orderBy)) : '',
            ],
            $baseSql
        );

        if ($this->limit !== null && $this->offset !== null) {
            $sql = sprintf('%s limit %d, %d', $sql, $this->offset, $this->limit);
        }

        return $sql;
    }

    /**
     * @param string      $where
     * @param null|string $key
     *
     * @return cashSelectQueryParts
     */
    public function addAndWhere($where, $key = null): cashSelectQueryParts
    {
        if ($key !== null) {
            $this->andWhere[(string) $key] = $where;
        } else {
            $this->andWhere[] = $where;
        }

        return $this;
    }

    /**
     * @param string      $join
     * @param null|string $key
     *
     * @return cashSelectQueryParts
     */
    public function addJoin($join, $key = null): cashSelectQueryParts
    {
        if ($key !== null) {
            $this->join[(string) $key] = $join;
        } else {
            $this->join[] = $join;
        }

        return $this;
    }

    /**
     * @param string      $select
     * @param null|string $key
     *
     * @return cashSelectQueryParts
     */
    public function addSelect($select, $key = null): cashSelectQueryParts
    {
        if ($key !== null) {
            $this->select[(string) $key] = $select;
        } else {
            $this->select[] = $select;
        }

        return $this;
    }

    /**
     * @param string      $orderBy
     * @param null|string $key
     *
     * @return cashSelectQueryParts
     */
    public function addOrderBy($orderBy, $key = null): cashSelectQueryParts
    {
        if ($key !== null) {
            $this->orderBy[(string) $key] = $orderBy;
        } else {
            $this->orderBy[] = $orderBy;
        }

        return $this;
    }

    /**
     * @param string      $groupBy
     * @param null|string $key
     *
     * @return cashSelectQueryParts
     */
    public function addGroupBy($groupBy, $key = null): cashSelectQueryParts
    {
        if ($key !== null) {
            $this->groupBy[(string) $key] = $groupBy;
        } else {
            $this->groupBy[] = $groupBy;
        }

        return $this;
    }

    /**
     * @param             $key
     * @param             $value
     * @param null|string $escape
     *
     * @return cashSelectQueryParts
     */
    public function addParam($key, $value, $escape = null): cashSelectQueryParts
    {
        if ($escape) {
            $value = $this->model->escape($value, 'like');
            switch ($escape) {
                case 'like_begin':
                    $value = "%$value";
                    break;

                case 'like_end':
                    $value = "$value%";
                    break;

                case 'like':
                    $value = "%$value%";
                    break;
            }
        }

        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param string $from
     * @param string $alias
     *
     * @return cashSelectQueryParts
     */
    public function from($from, $alias): cashSelectQueryParts
    {
        $this->from = $from;
        $this->alias = $alias;

        return $this;
    }

    /**
     * @param null $limit
     *
     * @return cashSelectQueryParts
     */
    public function limit($limit): cashSelectQueryParts
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param null $offset
     *
     * @return cashSelectQueryParts
     */
    public function offset($offset): cashSelectQueryParts
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param string $baseSql
     *
     * @return cashSelectQueryParts
     */
    public function setBaseSql(string $baseSql): cashSelectQueryParts
    {
        $this->baseSql = $baseSql;

        return $this;
    }

    /**
     * @param array $andWhere
     *
     * @return cashSelectQueryParts
     */
    public function andWhere(array $andWhere): cashSelectQueryParts
    {
        $this->andWhere = $andWhere;

        return $this;
    }

    /**
     * @param array $orderBy
     *
     * @return cashSelectQueryParts
     */
    public function orderBy(array $orderBy): cashSelectQueryParts
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @param array $select
     *
     * @return cashSelectQueryParts
     */
    public function select(array $select): cashSelectQueryParts
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @param array $join
     *
     * @return cashSelectQueryParts
     */
    public function join(array $join): cashSelectQueryParts
    {
        $this->join = $join;

        return $this;
    }

    /**
     * @param array $params
     *
     * @return cashSelectQueryParts
     */
    public function params(array $params): cashSelectQueryParts
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @param cashModel $model
     *
     * @return cashSelectQueryParts
     */
    public function setModel(cashModel $model): cashSelectQueryParts
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return array
     */
    public function getAndWhere(): array
    {
        return $this->andWhere;
    }

    /**
     * @return array
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @return array
     */
    public function getJoin(): array
    {
        return $this->join;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getBaseSql(): string
    {
        return $this->baseSql;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getGroupBy(): array
    {
        return $this->groupBy;
    }

    /**
     * @param array $groupBy
     *
     * @return cashSelectQueryParts
     */
    public function groupBy(array $groupBy): cashSelectQueryParts
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param cashSelectQueryParts $selectQueryParts
     * @param cashSelectQueryParts $selectQueryParts2
     *
     * @return cashSelectQueryParts
     * @throws waException
     */
    public static function union(
        cashSelectQueryParts $selectQueryParts,
        cashSelectQueryParts $selectQueryParts2
    ): cashSelectQueryParts {
        $unionSql = sprintf(
            '__SELECT_PART__  from ((%s)  union  (%s)) as union_table  __ORDER_BY_PART__',
            $selectQueryParts->getSql(),
            $selectQueryParts2->getSql()
        );
        $unionParts = new self(cash()->getModel());
        $unionParts
            ->setBaseSql($unionSql)
            ->select(['union_table.*'])
            ->limit($selectQueryParts->getLimit())
            ->offset($selectQueryParts->getOffset())
            ->orderBy(
                array_map(
                    static function ($order) use ($selectQueryParts) {
                        return str_replace($selectQueryParts->getAlias() . '.', 'union_table.', $order);
                    },
                    $selectQueryParts->getOrderBy()
                )
            )
            ->params(array_merge($selectQueryParts2->getParams(), $selectQueryParts->getParams()));

        return $unionParts;
    }
}
