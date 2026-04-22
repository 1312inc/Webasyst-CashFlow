<?php

final class cashApiContactSearchRequest
{
    private const MAX_LIMIT = 30;

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

    /**
     * @var null|int
     */
    private $limit;

    public function __construct(?string $term, ?int $categoryId, ?int $is_user, ?int $limit)
    {
        if ($limit > self::MAX_LIMIT) {
            throw new cashValidateException(sprintf('Max search limit is %d', self::MAX_LIMIT));
        }
        $this->limit = $limit;
        $this->term = (string) $term;
        $this->is_user = $is_user ?? null;
        $this->categoryId = empty($categoryId) ? null : $categoryId;
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

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
