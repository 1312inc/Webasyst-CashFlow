<?php

final class cashApiCategoryResponseDto
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
     * @var int|null
     */
    public $parent_category_id;

    /**
     * @var string|null
     */
    public $glyph;

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
        $dto->is_profit = $category->getIsProfit();
        $dto->glyph = $category->getGlyph();
        $dto->parent_category_id = $category->getCategoryParentId();

        return $dto;
    }
}
