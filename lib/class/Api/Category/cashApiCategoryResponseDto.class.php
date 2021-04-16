<?php

/**
 * Class cashApiCategoryResponseDto
 */
class cashApiCategoryResponseDto
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $color;

    /**
     * @var int
     */
    public $sort;

    /**
     * @var string
     */
    public $create_datetime;

    /**
     * @var string|null
     */
    public $update_datetime;

    /**
     * @var bool
     */
    public $is_profit;

    /**
     * @param cashCategory $category
     *
     * @return cashApiCategoryResponseDto
     */
    public static function fromCategory(cashCategory $category): cashApiCategoryResponseDto
    {
        $dto = new self();
        $dto->id = (int) $category->getId();
        $dto->name = $category->getName();
        $dto->color = $category->getColor();
        $dto->type = $category->getType();
        $dto->sort = (int) $category->getSort();
        $dto->create_datetime = $category->getCreateDatetime();
        $dto->update_datetime = $category->getUpdateDatetime();
        $dto->is_profit = (bool) $category->getIsProfit();

        return $dto;
    }
}
