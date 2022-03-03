<?php

final class cashApiContactGetListRequest
{
    private const MAX_LIMIT = 30;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    public function __construct(int $offset, int $limit)
    {
        if ($limit > self::MAX_LIMIT) {
            throw new cashValidateException(sprintf('Max limit is %d', self::MAX_LIMIT));
        }
        if ($limit <= 0) {
            throw new cashValidateException('Limit must be over 0');
        }

        if ($offset < 0) {
            throw new cashValidateException('Limit must be non negative');
        }

        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
