<?php

final class cashApiTransactionGetListRequest
{
    /**
     * @var DateTimeImmutable
     */
    private $from;

    /**
     * @var DateTimeImmutable
     */
    private $to;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $reverse;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var null|string Hash to filter transactions, can be "category/X", "account/X", "contractor/X", "currency/XXX",
     *      "import/X", "search/XXXX"
     */
    private $filter;

    public function __construct(
        ?DateTimeImmutable $from,
        ?DateTimeImmutable $to,
        ?int $offset,
        ?int $limit,
        ?string $filter,
        ?int $reverse
    ) {
        if (!$limit || $limit > 500 || $limit < 0) {
            $limit = 500;
        }

        if (!$to) {
            $to = DateTimeImmutable::createFromFormat('Y-m-d|', date('Y-m-d', strtotime('+25 years')));
        }

        if (!$from) {
            $from = DateTimeImmutable::createFromFormat('Y-m-d|', date('Y-m-d', strtotime('-25 years')));
        }

        if ($offset === null || $offset < 0) {
            $offset = 0;
        }

        $this->offset = $offset;
        $this->from = $from;
        $this->to = $to;
        $this->limit = $limit;
        $this->reverse = $reverse;
        $this->filter = $filter;
    }

    public function getFrom(): DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): DateTimeImmutable
    {
        return $this->to;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function getReverse(): bool
    {
        return !$this->reverse;
    }
}
