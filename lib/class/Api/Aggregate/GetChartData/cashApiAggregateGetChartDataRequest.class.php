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

    /**
     * @var int
     */
    private $company_id;

    public function __construct(DateTimeImmutable $from, DateTimeImmutable $to, string $groupBy, string $filter, $company_id = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->groupBy = $groupBy;
        $this->filter = $filter;
        $this->company_id = $company_id;
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

    public function getCompanyId(): ?int
    {
        return $this->company_id;
    }
}
