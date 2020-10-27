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
     * cashSelectQueryParts constructor.
     *
     * @param cashModel $model
     */
    public function __construct(cashModel $model)
    {
        $this->model = $model;
        $this->baseSql = <<<SQL
select __SELECT_PART__
from __FROM_PART__
__JOIN_PART__
__WHERE_PART__
__ORDER_PART__
SQL;

    }

    /**
     * @param $baseSql
     *
     * @return waDbResultSelect
     */
    public function query($baseSql = '')
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
                '__ORDER_PART__',
            ],
            [
                implode(",\n", $this->select),
                $this->from,
                $this->join ? implode("\n", $this->join) : '',
                $this->andWhere ? sprintf('where (%s)', implode(") \n and (", $this->andWhere)) : '',
                $this->orderBy ? sprintf('order by %s', implode(",\n", $this->orderBy)) : '',
            ],
            $baseSql
        );

        if ($this->limit !== null && $this->offset !== null) {
            $sql = sprintf('%s limit %d, %d', $sql, $this->offset, $this->limit);
        }

        return $this->model->query($sql, $this->params);
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
     * @param string $join
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
     * @param string $select
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
     * @param string $orderBy
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
     * @param $key
     * @param $value
     *
     * @return cashSelectQueryParts
     */
    public function addParam($key, $value): cashSelectQueryParts
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param string $from
     *
     * @return cashSelectQueryParts
     */
    public function from(string $from): cashSelectQueryParts
    {
        $this->from = $from;

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
    public function setParams(array $params): cashSelectQueryParts
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
}
