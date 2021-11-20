<?php

class cashCategory extends cashAbstractEntity
{
    use kmwaEntityDatetimeTrait;

    public const TYPE_INCOME  = 'income';
    public const TYPE_EXPENSE  = 'expense';
    public const TYPE_TRANSFER = 'transfer';

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
     * @var null|string
     */
    private $color;

    /**
     * @var null|int
     */
    private $sort = 0;

    /**
     * @var bool
     */
    private $is_profit = false;

    /**
     * @var int|null
     */
    private $category_parent_id;

    /**
     * @var string|null
     */
    private $glyph;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): cashCategory
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): cashCategory
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): cashCategory
    {
        $this->type = $type;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(?string $color): cashCategory
    {
        $this->color = $color;

        return $this;
    }

    public function getSort(): int
    {
        return (int) $this->sort;
    }

    public function setSort(?int $sort): cashCategory
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

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function isIncome(): bool
    {   
        return $this->type === self::TYPE_INCOME;
    }

    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    public function isSystem(): bool
    {
        return $this->id < 0;
    }

    public function getIsProfit(): bool
    {
        return $this->is_profit;
    }

    public function setIsProfit(bool $is_profit): cashCategory
    {
        $this->is_profit = $is_profit;

        return $this;
    }

    public function getCategoryParentId(): ?int
    {
        return $this->category_parent_id;
    }

    public function setCategoryParentId(?int $parent_category_id): cashCategory
    {
        $this->category_parent_id = $parent_category_id;

        return $this;
    }

    public function getGlyph(): ?string
    {
        return $this->glyph;
    }

    public function setGlyph(?string $glyph): cashCategory
    {
        $this->glyph = $glyph;

        return $this;
    }

    public function afterHydrate($data = [])
    {
        $this->is_profit = (bool) $data['is_profit'];
    }

    public function afterExtract(array &$fields)
    {
        $fields['is_profit'] = (int) $fields['is_profit'];
    }
}
