<?php

use ApiPack1312\Exception\ApiWrongParamException;

class cashApiCategoryCreateRequest
{
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
     * @var bool|null
     */
    private $isProfit;

    /**
     * @var int|null
     */
    private $parentCategoryId;

    /**
     * @var string|null
     */
    private $glyph;

    public function __construct(
        string $name,
        string $type,
        string $color,
        ?int $sort,
        ?bool $isProfit,
        ?int $parentCategoryId,
        ?string $glyph
    ) {
        if (empty($name)) {
            throw new ApiWrongParamException('name',_w('Empty category name'));
        }

        $this->name = $name;
        $this->type = $type;
        $this->color = $color;
        $this->sort = (int) $sort;
        $this->isProfit = (bool) $isProfit;
        $this->parentCategoryId = $parentCategoryId;
        $this->glyph = $glyph;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getIsProfit(): ?bool
    {
        return $this->isProfit;
    }

    public function getParentCategoryId(): ?int
    {
        return $this->parentCategoryId;
    }

    public function getGlyph(): ?string
    {
        return $this->glyph;
    }
}
