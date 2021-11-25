<?php

final class cashApiTransactionGetShrinkListRequest
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
     * @var string Hash to filter transactions, can be "category/X", "account/X", "contractor/X", "currency/XXX"
     */
    private $filter;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to, string $filter)
    {
        $this->from = $from;
        $this->to = $to;
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

    public function getFilter(): string
    {
        return $this->filter;
    }
}
