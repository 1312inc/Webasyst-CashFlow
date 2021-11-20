<?php

/**
 * Class cashApiAggregateGetChartDataRequest
 */
final class cashApiAggregateGetChartDataRequest
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
     * @var string
     */
    private $groupBy;

    /**
     * @var string
     */
    private $filter;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to, string $groupBy, string $filter)
    {
        $this->from = $from;
        $this->to = $to;
        $this->groupBy = $groupBy;
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

    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }
}
