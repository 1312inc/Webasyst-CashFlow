<?php

final class cashAutocompleteParamsDto
{
    /**
     * @var string
     */
    private $term;

    /**
     * @var null|int
     */
    private $categoryId;

    public function __construct(string $term, ?int $categoryId)
    {
        $this->term = trim($term);
        $this->categoryId = $categoryId;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }
}
