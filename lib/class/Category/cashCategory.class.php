<?php

/**
 * Class cashCategory
 */
class cashCategory extends cashAbstractEntity
{
    use kmwaEntityDatetimeTrait;

    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_TRANSFER = 'transfer';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $color;

    /**
     * @var int
     */
    private $sort;

    /**
     * @var int|bool
     */
    private $is_profit = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return cashCategory
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return cashCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return cashCategory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return cashCategory
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     *
     * @return cashCategory
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return bool
     */
    public function beforeSave()
    {
        $this->updateCreateUpdateDatetime();

        return true;
    }

    /**
     * @return bool
     */
    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    /**
     * @return bool
     */
    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    /**
     * @return bool
     */
    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->id < 0;
    }

    /**
     * @return bool|int
     */
    public function getIsProfit()
    {
        return $this->is_profit;
    }

    /**
     * @param bool|int $is_profit
     *
     * @return cashCategory
     */
    public function setIsProfit($is_profit): cashCategory
    {
        $this->is_profit = $is_profit;

        return $this;
    }
}
