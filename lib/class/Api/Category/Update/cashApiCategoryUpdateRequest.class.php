<?php

final class cashApiCategoryUpdateRequest extends cashApiCategoryCreateRequest
{
    /**
     * @var int
     */
    private $id;

    public function __construct(
        int $id,
        string $name,
        string $type,
        string $color,
        int $sort,
        ?bool $isProfit,
        ?int $parentCategoryId,
        ?string $glyph
    ) {
        $this->id = $id;

        parent::__construct($name, $type, $color, $sort, $isProfit, $parentCategoryId, $glyph);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
