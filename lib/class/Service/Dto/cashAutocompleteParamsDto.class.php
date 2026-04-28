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

    /**
     * @var null|int
     */
    private $is_user;

    public function __construct(string $term, ?int $categoryId, ?int $is_user)
    {
        $this->term = trim($term);
        $this->categoryId = $categoryId;
        $this->is_user = $is_user;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function isUser(): ?int
    {
        return $this->is_user;
    }
}
