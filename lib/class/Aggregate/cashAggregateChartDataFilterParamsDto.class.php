<?php

/**
 * Class cashAggregateChartDataFilterParamsDto
 */
final class cashAggregateChartDataFilterParamsDto
{
    public const GROUP_BY_DAY   = 'day';
    public const GROUP_BY_MONTH = 'month';
    public const GROUP_BY_YEAR  = 'year';

    /**
     * @var cashAggregateFilter
     */
    public $filter;

    /**
     * @var DateTimeImmutable
     */
    public $from;

    /**
     * @var DateTimeImmutable
     */
    public $to;

    /**
     * @var string
     */
    public $groupBy;

    /**
     * @var waContact
     */
    public $contact;

    /**
     * cashAggregateChartDataFilterParamsDto constructor.
     *
     * @param                     $contact
     * @param                     $from
     * @param                     $to
     * @param                     $groupBy
     * @param cashAggregateFilter $filter
     */
    public function __construct($contact, $from, $to, $groupBy, cashAggregateFilter $filter)
    {
        $this->from = $from;
        $this->to = $to;
        $this->groupBy = $groupBy;
        $this->contact = $contact;
        $this->filter = $filter;
    }
}
